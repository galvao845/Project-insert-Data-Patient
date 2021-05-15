<?php
namespace Zeedhi\Framework\Util;

/**
 * Class Crypt
 *
 * Class used for encrypt and decrypt data through salt's
 *
 * @package Zeedhi\Framework\Util
 * 
 * @version 2.0.0
 */
class Crypt {

    const IV_SIZE = 16;
    const TEXT_SIZE = 32;
    

    /**
     * Pad the text
     * 
     * Pad the text based on the normalization of string size to be encrypted
     * 
     * @param $salt
     * @return string
     */
    protected static function padText($text, $size) {
        if (strlen($text) % $size) 
            return str_pad($text, strlen($text) + $size - strlen($text) % $size, "\0");
        return $text;
    }

    protected static function getIv($cipher){
        return openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
    }

    private static function encodeEncryptedText($text, $iv){
        $iv_pos = rand(1, 15);
        $text = substr_replace($text, $iv, $iv_pos, 0);
        $iv_pos = dechex($iv_pos);
        $text = substr_replace($text, $iv_pos, 0, 0);
        $text = base64_encode($text);

        return $text;
    }

    /**
     * Encrypt data with salt.
     *
     * @param string $text Value to be encrypt.
     * @param string $salt Salt to be encrypt.
     *
     * @return string
     */
    public static function encrypt($text, $salt)
    {
        try{
            $cipher = "AES-256-CBC";
            $iv = self::getIv($cipher);
            $text = self::padText($text, 32);
            $text = 
                openssl_encrypt(
                $text, 
                $cipher, 
                $salt, 
                OPENSSL_NO_PADDING, 
                $iv
            );
            $text = self::encodeEncryptedText($text, $iv);            
        
            return $text;
        }
        catch(Exception $e){
            throw Exception::internalError();
        }
    }

    private static function decodeEncryptedText($text){
        $text = base64_decode($text);
        $iv_pos = hexdec($text[0])+1;
        $iv = substr($text, $iv_pos, self::IV_SIZE);
        $text_arr = explode($iv, $text);
        $text_arr[0] = substr($text_arr[0], 1);
        $text = implode('', $text_arr);
        $iv = self::padText($iv, self::IV_SIZE);

        return array('text'=> $text, 'iv'=>$iv);
    }

    /**
     * Decrypt data through salt.
     *
     * @param string $text Value to be decrypt.
     * @param string $salt Salt used in encryption.
     *
     * @return string
     */
    public static function decrypt($text, $salt)
    {
        try {
            $cipher = "AES-256-CBC";
            $decoded = self::decodeEncryptedText($text);
            
            $text = 
            openssl_decrypt(
                $decoded['text'], 
                $cipher, 
                $salt, 
                OPENSSL_NO_PADDING, 
                $decoded['iv']
            );
            $text = trim($text);
            return $text;
        }
        catch(Exception $e){
            throw Exception::internalError();            
        }
    }
} 