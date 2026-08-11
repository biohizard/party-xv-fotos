<?php
/*
 * jQuery droply Plugin; v2017FEB12
 * https://www.itechflare.com/
 * Copyright (c) 2015-2017 iTechFlare; Licensed: GPL + MIT
 * Version : v1.7.1
 * Developer: (mindsquare)
 *
 * MODIFICADO: generación automática de thumbnails para imágenes.
 * - JPG / JPEG
 * - PNG
 * - GIF (primer frame)
 * - WEBP (si GD tiene soporte WebP)
 *
 * Los thumbnails se guardan por defecto en:
 *   <uploadFileDestinationURL>/thumbnails/
 *
 * Requiere la extensión GD de PHP.
 */

class Droply_Processor
{
    // Change this list to allow more files to be uploaded
    private $configuration;
    private $chunkEnabled;
    private $allowedExts;

    public function __construct($configration = array(), $allowedExtensions = array('gif', 'jpg', 'jpeg', 'png', 'webp'))
    {
        // Initialize configuration
        $this->configuration = array(
            'uploadFileDestinationURL' => 'uploads', // From server side, define the uploads folder url
            'maxFileSize' => 1024 * 1024 * 10, // Max 10MB
            'fileNameFormat' => '',
            'emailNotification' => false,
            'adminToEmail' => 'test@test.com',
            'emailSubject' => 'New file has been uploaded',

            // ===== THUMBNAILS =====
            'thumbnailEnabled' => true,
            'thumbnailFolder' => 'thumbnails',
            'thumbnailWidth' => 240,
            'thumbnailHeight' => 160,
            'thumbnailQuality' => 85,
            'thumbnailPrefix' => 'thumb_',
        );

        // Update configuration with user configuration
        $this->configuration = array_merge($this->configuration, $configration);

        // Force adding separator at the end of the upload path
        $path = rtrim($this->configuration['uploadFileDestinationURL'], '/\\') . DIRECTORY_SEPARATOR;
        $this->configuration['uploadFileDestinationURL'] = $path;

        // Normalize thumbnail folder
        $thumbFolder = trim($this->configuration['thumbnailFolder'], '/\\');
        $this->configuration['thumbnailFolder'] = $thumbFolder . DIRECTORY_SEPARATOR;

        $this->allowedExts = $allowedExtensions;
        $this->chunkEnabled = true;

        $this->verify_upload_folder();
        $this->verify_thumbnail_folder();
    }

    public function __header()
    {
        // Make sure file is not cached (as it happens for example on iOS devices)
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }

    public function add_extension($ext_list)
    {
        $this->allowedExts = array_merge($this->allowedExts, $ext_list);
    }

    public function process_upload()
    {
        $this->__header();
        $json = array();

        // Mantiene compatibilidad con el código original.
        if (defined('FILTER_SANITIZE_STRING')) {
            $_REQUEST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            if (!is_array($_REQUEST)) {
                $_REQUEST = $_POST;
            }
        } else {
            $_REQUEST = $_POST;
        }

        $chunk_enabled = isset($_REQUEST['chunk-upload']) ? $_REQUEST['chunk-upload'] : "false";

        // Check if it was a delete command
        if (isset($_REQUEST['command']) && $_REQUEST['command'] == 'delete') {
            if (isset($_REQUEST['droplyfn'])) {
                $filename = base64_decode($_REQUEST['droplyfn']);
                $this->delete_file($filename);
            } else {
                $json['error'] = "File not found";
                $json['status'] = 'false';
                echo json_encode($json);
                die();
            }
        }

        // Check if chunk upload is enabled
        if ($chunk_enabled == "false") {
            $this->chunkEnabled = false;
            $filename = $this->filter_filename($_FILES['SelectedFile']['name']);
            $temp = explode(".", $_FILES['SelectedFile']['name']);
        } else {
            $this->chunkEnabled = true;
            $filename = $this->filter_filename($_REQUEST['file-name']);
            $temp = explode(".", $filename);
        }

        $allowedE = $this->check_extension_validity($temp);
        $allowedS = $this->check_file_size();

        if (!$this->chunkEnabled) {
            $json = $this->handle_regular_upload($allowedE, $allowedS);
        } else {
            $json = $this->handle_chunk_upload($allowedE, $allowedS);
        }

        $this->finished_processing($json);
    }

    public function filter_filename($filename)
    {
        $filename = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $filename);
        $filename = mb_ereg_replace("([\.]{2,})", '', $filename);
        return $filename;
    }

    public function check_extension_validity($temp)
    {
        $this->allowedExts = array_map('strtolower', $this->allowedExts);
        $extension = strtolower(trim(end($temp), " "));

        if (in_array($extension, $this->allowedExts)) {
            return true;
        }

        $json = array('status' => 'false');
        if ($this->chunkEnabled) {
            $json['error'] = (isset($_REQUEST['file-type']) ? $_REQUEST['file-type'] : $extension) . ': Invalid file type!';
        } else {
            $json['error'] = $_FILES['SelectedFile']['type'] . ': Invalid file type!';
        }

        echo json_encode($json);
        die();
    }

    public function handle_chunk_upload($allowedE, $allowedS)
    {
        $json = array();
        $json['status'] = 'false';
        $fileIndex = '';

        if (isset($_REQUEST['file-name'])) {
            $fileName = $this->filter_filename($_REQUEST['file-name']);
        } else {
            $json['msg'] = 'Invalid request.';
            echo json_encode($json);
            die();
        }

        if (isset($_REQUEST['fileIndx'])) {
            $fileIndex = intval($_REQUEST['fileIndx']);
        }

        $filename = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $filePath = $this->configuration['uploadFileDestinationURL'] . $fileName;

        $chunk = isset($_REQUEST['chunk']) ? intval($_REQUEST['chunk']) : 0;
        $chunks = isset($_REQUEST['chunks']) ? intval($_REQUEST['chunks']) : 0;

        if (!$out = @fopen("{$filePath}.{$fileIndex}.part", $chunks ? 'ab' : 'wb')) {
            $json['msg'] = 'Failed to open output stream.';
            echo json_encode($json);
            die();
        }

        if (!empty($_FILES)) {
            if ($_FILES['file']['error'] || !is_uploaded_file($_FILES['file']['tmp_name'])) {
                $json['msg'] = 'Failed to move uploaded file.';
                echo json_encode($json);
                die();
            }

            if (!$in = @fopen($_FILES['file']['tmp_name'], 'rb')) {
                $json['msg'] = 'Failed to open input stream.';
                echo json_encode($json);
                die();
            }
        } else {
            if (!$in = @fopen('php://input', 'rb')) {
                $json['msg'] = 'Failed to open input stream.';
                echo json_encode($json);
                die();
            }
        }

        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);

        // File complete
        if (!$chunks || $chunk == $chunks) {
            if ($this->configuration['emailNotification']) {
                $this->send_email_notification();
            }

            if ($this->configuration['fileNameFormat'] == '') {
                $newname = $filename . '-' . time() . '-' . rand(10, 1000) . '.' . $extension;
            } else {
                $newname = $this->configuration['fileNameFormat'] . '-' . time() . '-' . rand(10, 1000) . '.' . $extension;
            }

            $filePath2 = $this->configuration['uploadFileDestinationURL'] . $newname;

            // Strip the temp .part suffix off
            if (!rename("{$filePath}.{$fileIndex}.part", $filePath2)) {
                $json['status'] = 'false';
                $json['msg'] = 'Could not finalize uploaded file.';
                echo json_encode($json);
                die();
            }

            // ===== GENERAR THUMBNAIL AL TERMINAR TODOS LOS CHUNKS =====
            $thumbnail = $this->create_thumbnail($filePath2, $newname);

            $json['status'] = 'true';
            $json['msg'] = $fileName . ' Has been successfully uploaded!';
            $json['newFileName'] = $newname;
            $json['fullPath'] = $filePath2;

            if ($thumbnail !== false) {
                $json['thumbnail'] = $thumbnail;
            }
        }

        if (is_array($json)) {
            $json['status'] = 'true';
        } else {
            $json = array('status' => 'true');
        }

        echo json_encode($json);
        die();
    }

    public function send_email_notification()
    {
        $msg = "New file has been uploaded\nStorage location: " . $this->configuration['uploadFileDestinationURL'];
        try {
            mail($this->configuration['adminToEmail'], $this->configuration['emailSubject'], $msg);
        } catch (Exception $exp) {
            error_log('Droply: Email notification failed');
        }
    }

    public function handle_regular_upload($allowedE, $allowedS)
    {
        $json = array();
        $json['status'] = 'false';

        if ($allowedE && $allowedS) {
            if ($_FILES['SelectedFile']['error'] > 0) {
                $json['msg'] = 'Return Code: ' . $_FILES['SelectedFile']['error'];
            } else {
                if (!file_exists($this->configuration['uploadFileDestinationURL'] . time() . '-' . $_FILES['SelectedFile']['name'])) {
                    $filename = pathinfo($_FILES['SelectedFile']['name'], PATHINFO_FILENAME);
                    $extension = pathinfo($_FILES['SelectedFile']['name'], PATHINFO_EXTENSION);

                    if ($this->configuration['fileNameFormat'] == '') {
                        $newname = $filename . '-' . time() . '-' . rand(10, 1000) . '.' . $extension;
                    } else {
                        $newname = $this->configuration['fileNameFormat'] . '-' . time() . '-' . rand(10, 1000) . '.' . $extension;
                    }

                    $fullPath = $this->configuration['uploadFileDestinationURL'] . $newname;

                    if (!move_uploaded_file($_FILES['SelectedFile']['tmp_name'], $fullPath)) {
                        $json['status'] = 'false';
                        $json['msg'] = 'Failed to move uploaded file.';
                        return $json;
                    }

                    // ===== GENERAR THUMBNAIL EN SUBIDA NORMAL =====
                    $thumbnail = $this->create_thumbnail($fullPath, $newname);

                    if ($this->configuration['emailNotification']) {
                        $this->send_email_notification();
                    }

                    $json['status'] = 'true';
                    $json['msg'] = $_FILES['SelectedFile']['name'] . ' Has been successfully uploaded!';
                    $json['newFileName'] = $newname;
                    $json['fullPath'] = $fullPath;

                    if ($thumbnail !== false) {
                        $json['thumbnail'] = $thumbnail;
                    }
                } else {
                    $json['msg'] = 'File already exist!';
                    echo json_encode($json);
                    die();
                }
            }
        }

        return $json;
    }

    /**
     * Genera una miniatura JPEG respetando proporción.
     * Devuelve la ruta del thumbnail o false si no aplica/no pudo generarse.
     */
    private function create_thumbnail($sourceFile, $originalFileName)
    {
        if (!$this->configuration['thumbnailEnabled']) {
            return false;
        }

        if (!extension_loaded('gd')) {
            error_log('Droply thumbnail: PHP GD extension is not enabled.');
            return false;
        }

        if (!is_file($sourceFile)) {
            return false;
        }

        $extension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
        $imageExtensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');

        if (!in_array($extension, $imageExtensions)) {
            return false;
        }

        $imageInfo = @getimagesize($sourceFile);
        if ($imageInfo === false) {
            return false;
        }

        $mime = isset($imageInfo['mime']) ? strtolower($imageInfo['mime']) : '';
        $sourceImage = false;

        switch ($mime) {
            case 'image/jpeg':
                $sourceImage = @imagecreatefromjpeg($sourceFile);
                break;

            case 'image/png':
                $sourceImage = @imagecreatefrompng($sourceFile);
                break;

            case 'image/gif':
                $sourceImage = @imagecreatefromgif($sourceFile);
                break;

            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $sourceImage = @imagecreatefromwebp($sourceFile);
                }
                break;
        }

        if (!$sourceImage) {
            return false;
        }

        $originalWidth = imagesx($sourceImage);
        $originalHeight = imagesy($sourceImage);

        if ($originalWidth <= 0 || $originalHeight <= 0) {
            imagedestroy($sourceImage);
            return false;
        }

        $maxWidth = max(1, intval($this->configuration['thumbnailWidth']));
        $maxHeight = max(1, intval($this->configuration['thumbnailHeight']));

        // No agrandar imágenes pequeñas; solo reducir cuando sea necesario.
        $scale = min(
            $maxWidth / $originalWidth,
            $maxHeight / $originalHeight,
            1
        );

        $thumbWidth = max(1, (int) round($originalWidth * $scale));
        $thumbHeight = max(1, (int) round($originalHeight * $scale));

        $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);

        // Fondo blanco para que PNG/GIF transparentes no queden negros al convertir a JPG.
        $white = imagecolorallocate($thumbnail, 255, 255, 255);
        imagefill($thumbnail, 0, 0, $white);

        imagecopyresampled(
            $thumbnail,
            $sourceImage,
            0,
            0,
            0,
            0,
            $thumbWidth,
            $thumbHeight,
            $originalWidth,
            $originalHeight
        );

        $thumbnailDir = $this->get_thumbnail_directory();
        if (!$this->ensure_directory($thumbnailDir)) {
            imagedestroy($sourceImage);
            imagedestroy($thumbnail);
            return false;
        }

        $baseName = pathinfo($originalFileName, PATHINFO_FILENAME);
        $thumbnailName = $this->configuration['thumbnailPrefix'] . $baseName . '.jpg';
        $thumbnailPath = $thumbnailDir . $thumbnailName;

        $quality = intval($this->configuration['thumbnailQuality']);
        $quality = max(0, min(100, $quality));

        $saved = imagejpeg($thumbnail, $thumbnailPath, $quality);

        imagedestroy($sourceImage);
        imagedestroy($thumbnail);

        if (!$saved) {
            return false;
        }

        return $thumbnailPath;
    }

    private function get_thumbnail_directory()
    {
        return $this->configuration['uploadFileDestinationURL'] . $this->configuration['thumbnailFolder'];
    }

    private function ensure_directory($path)
    {
        if (is_dir($path)) {
            return true;
        }

        return @mkdir($path, 0766, true) || is_dir($path);
    }

    public function verify_upload_folder()
    {
        $path = $this->configuration['uploadFileDestinationURL'];

        if (!file_exists($path)) {
            mkdir($path, 0766, true);
            @touch($path . 'index.php');
        }
    }

    private function verify_thumbnail_folder()
    {
        if (!$this->configuration['thumbnailEnabled']) {
            return;
        }

        $path = $this->get_thumbnail_directory();
        if (!file_exists($path)) {
            mkdir($path, 0766, true);
            @touch($path . 'index.php');
        }
    }

    public function check_file_size()
    {
        if ($this->chunkEnabled) {
            $fileSize = intval($_REQUEST['file-size']);
        } else {
            $fileSize = intval($_FILES['SelectedFile']['size']);
        }

        if ($fileSize < $this->configuration['maxFileSize']) {
            return true;
        }

        $json = array();
        $json['status'] = 'false';
        $json['error'] = 'File size has exceeded the limit (' . $this->configuration['maxFileSize'] . ')!';
        echo json_encode($json);
        die();
    }

    public function finished_processing($json)
    {
        echo json_encode($json);
        die();
    }

    public function delete_file_by_name($fileName)
    {
        // Please handle deletion safely, as this part could be exploited by hackers.
        // El Droply original deja esta función vacía.
    }

    public function delete_file($filename)
    {
        $filename = $this->filter_filename($filename);
        $this->delete_file_by_name($filename);
        echo $filename . ' Has been deleted!';
        die();
    }
}