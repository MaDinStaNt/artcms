<?php
/*
        resample images to the desired dimentions

        Better to use for creting thumnails from Large images

        Supports GIF/JPG/PNG formats

        Example:
        require_once(FUNCTION_PATH . 'functions.image.php');
        $img_name = $this->template_vars['HTTP'].'pub/'.$show_feat->get_field('prod_hg_image');
        $new_width = 85; $new_height =81;
        create_thumb($img_name, $new_width, $new_height);
*/

function create_thumb($control_name, $img_name, $new_width, $new_height, $new_img_name)
{
        global $FilePath;
        $type = explode("/", strtolower($_FILES[$control_name]['type']));
        if (isset($type[1]))
        {
                switch ($type[1])
                {
                        case 'jpg':
                                if ( function_exists('imagecreatefromjpeg')) {
                                        $src_img = ImageCreateFromJPEG($img_name); break;
                                }
                        case 'jpeg':
                                if ( function_exists('imagecreatefromjpeg')) {
                                        $src_img = ImageCreateFromJPEG($img_name); break;
                                }
                        case 'pjpeg':
                                if ( function_exists('imagecreatefromjpeg')) {
                                        $src_img = ImageCreateFromJPEG($img_name); break;
                                }
                        case 'gif':
                                if ( function_exists('imagecreatefromgif')) {
                                        $src_img = ImageCreateFromGIF($img_name); break;
                                }
                        case 'png':
                                if ( function_exists('imagecreatefrompng')) {
                                        $src_img = ImageCreateFromPNG($img_name); break;
                                }
                        case 'x-png':
                                if ( function_exists('imagecreatefrompng')) {
                                        $src_img = ImageCreateFromPNG($img_name); break;
                                }
                        default:
                                return 0;
        }

        $size=GetImageSize($img_name);

        $w = $size[0];
        $h = $size[1];

        (int)$w = ($w <= $h) ? round(($w * $new_height)/$h) : $new_width;
        (int)$h = ($w > $h) ? round(($h * $new_width)/$w) : $new_height;

        $thumb = imagecreatetruecolor($w, $h);
        imagecopyresized($thumb, $src_img,0,0,0,0, $w, $h, ImageSX($src_img), ImageSY($src_img));

        switch ($type[1])
        {
                case 'jpg':
                        if (function_exists("imagejpeg")) {
                                header ("Content-type: image/jpg");
                                ImageJPEG($thumb, $FilePath . 'pub/' . $new_img_name);        break;
                        }
                case 'jpeg':
                        if (function_exists("imagejpeg")) {
                                header ("Content-type: image/jpg");
                                ImageJPEG($thumb, $FilePath . 'pub/' . $new_img_name);        break;
                        }
                case 'pjpeg':
                        if (function_exists("imagejpeg")) {
                                header ("Content-type: image/jpg");
                                ImageJPEG($thumb, $FilePath . 'pub/' . $new_img_name);        break;
                        }
                case 'gif':
                        if (function_exists("imagegif")) {
                                header ("Content-type: image/gif");
                                ImageGIF($thumb, $FilePath . 'pub/' . $new_img_name);        break;
                        }
                case 'png':
                        if (function_exists("imagepng")) {
                                header ("Content-type: image/png");
                                ImagePNG($thumb, $FilePath . 'pub/' . $new_img_name);        break;
                        }
                case 'x-png':
                        if (function_exists("imagepng")) {
                                header ("Content-type: image/png");
                                ImagePNG($thumb, $FilePath . 'pub/' . $new_img_name);        break;
                        }
        }

        ImageDestroy($src_img);
        ImageDestroy($thumb);

                }
}

function get_image_width($path)
{
        if (is_file($path))
        {
                $a = @getimagesize($path);
                if (isset($a[0]))
                        return intval($a[0]);
                else
                        return 0;
        }
        else
                return 0;
}

function get_image_height($path)
{
        if (is_file($path))
        {
                $a = @getimagesize($path);
                if (isset($a[1]))
                        return intval($a[1]);
                else
                        return 0;
        }
        else
                return 0;
}

?>