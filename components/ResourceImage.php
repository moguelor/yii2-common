<?php

namespace jmoguelruiz\yii2\common\components;

use Imagine\Exception\Exception;
use Imagine\Image\Box;
use \ImageOptimizer\OptimizerFactory;
use swapwink\common\models\User;
use Yii;
use yii\helpers\Html;
use yii\imagine\Image;

class ResourceImage
{
    const PLAYER = 1;

    /** Entornos **/
    const ENVIROMENT_DEV = "dev";
    const ENVIROMENT_PROD = "prod";
    const ENVIROMENT_TEST = "test";
    
    /** Directorios Base **/
    const BASE_PATH_PROD = "frontend/web/images";
    const BASE_PATH_DEV = "frontend/web/images/dev";
    const BASE_PATH_TEST = "frontend/web/images/test";
    
    /** Medidas soportadas **/
    const SIZE_ORIGINAL = 0;
    const SIZE_THUMB = 1;
    
    private static $_base = 'images';
    private static $_prefix = "_";
    private static $_player = "player";
    private static $_temp = "temp";
    private static $_default = "default.png";
    private static $_enviroment = "dev";
    private static $_img = "img";
    private static $_thumb = "thumb";

    /**
     * Genera nombre del recurso a guardar.
     *
     * @param int    $id  Identificador del recurso
     * @param string $ext Extensión del recurso
     *
     * @return string Nombre del recurso
     */
    public static function setName($id, $ext)
    {
        return $id . self::$_prefix . time() . "." . $ext;
    }
    
    /**
     * 
     * Obtener el string del tamaño de la imagen.
     * 
     * @param int $imageSize Identificador del tamaño de la imagen. Si no esta especificada envia
     *                       por defecto la correspondiente a self::SIZE_THUMB.
     * @return string Tamaño de la imagen.
     */
    public static function getImageSize($imageSize){
        
        $imageSizes = [
            self::SIZE_ORIGINAL => '', 
            self::SIZE_THUMB => DIRECTORY_SEPARATOR . self::$_thumb
        ];
        
        return !empty($imageSizes[$imageSize]) ? $imageSizes[$imageSize] : $imageSize[self::SIZE_THUMB];
        
    }
    
    /**
     * Obtiene el nombre del recurso de acuerdo al tipo proporcionado.
     *
     * @param int  $type    Tipo del recurso 
     * @param bool $imageSize Tamaño de la imagen.
     *
     * @return string nombre del recurso
     */
    public static function getResource($type, $imageSize)
    {
        switch ($type) {
            case self::LOGO:
                $resource = self::$_player . DIRECTORY_SEPARATOR . self::$_player .  self::getImageSize($imageSize);
                break;
            default :
                $resource = '';
                break;
        }

        return $resource;
    }

    /**
     * Obtiene el directorio de acuerdo al tipo de recurso.
     *
     * @param int  $type      Tipo de recurso
     * @param bool $is_primal TRUE si es el path principal, FALSE si es path temporal. Por defecto es principal
     *
     * @return string ruta del directorio del recurso
     * 
     * Examples : 
     * 
     * getDirectory(ResourceImage::AVATAR, true)
     * Result: [PATH]/core/images/dev/avatar/ 
     * 
     * getDirectory(ResourceImage::AVATAR, false)
     * Result: [PATH]/core/images/dev/avatar_temp/ 
     * 
     * Nota: Las rutas generadas son absolutas.
     */
    public static function getDirectory($type, $is_primal = true)
    {
        $resource = self::getResource($type);

        return self::getContainer($resource, $is_primal);
    }

    /**
     * Obtiene la ruta del recurso de acuerdo con los parametros: Nombre y Tipo.
     *
     * @param string $name      Nombre del recurso
     * @param int    $type      Tipo del recurso
     * @param bool   $is_primal TRUE si es el path principal, FALSE si es path temporal. Por defecto es principal
     * @param bool   $sizeImage   Tamaño de la imagen.
     *
     * @return string Devuelve la ruta del recurso
     * 
     * Examples:
     * 
     * getResourcePath('1_8883992123.jpg',ResourceImage::AVATAR, true, true)
     * Result:  [PATH]/core/images/dev/avatar/1_8883992123.jpg
     * 
     * getResourcePath('1_8883992123.jpg',ResourceImage::AVATAR, true, false)
     * Result : images/dev/avatar/1_8883992123.jpg
     * 
     * getResourcePath('1_8883992123.jpg',ResourceImage::AVATAR, false, true)
     * Result : [PATH]/core/images/dev/avatar_temp/1_8883992123.jpg
     * 
     * getResourcePath('1_8883992123.jpg',ResourceImage::AVATAR, false, false)
     * Result : images/dev/avatar_temp/1_8883992123.jpg
     * 
     * Si thumb es True:
     * Result:  [PATH]/core/images/dev/avatar/thumb/1_8883992123.jpg
     */
    public static function getResourcePath($name, $type, $is_primal = true, $is_absolute = true, $sizeImage = self::SIZE_ORIGINAL)
    {
        $resource = self::getResource($type, $sizeImage);

        return self::getPath($resource, $name, $is_primal, $is_absolute);
    }
    
    /**
     * TODO: Los parametros de la función se estan volviendo excesivos, modularizar
     * a métodos separados, para obtener imagen del perfil.
     */

    /**
     * Obtiene la url del recurso de acuerdo a los parametros: Nombre y Tipo.
     *
     * @param string $name      Nombre del recurso
     * @param int    $type      Tipo del recurso
     * @param arr    $options   Opciones extra para obtener la url.
     * Por defecto son las siguientes:
     * [
     *    "isPrimal" => true   - TRUE si es el path principal, FALSE si es path temporal. Por defecto es principal
     *    "sizeImage" => self::SIZE_THUMB, - Tamaño de la imagen.
     *    "extraParams" => [
     *         "gender" => User::MAN - Genero del usuario.
     *    ] 
     * ]
     *
     * @return string Url del recurso
     * 
     * Examples:
     * getUrl('1_8883992123.jpg',ResourceImage::AVATAR, true, null)
     * Result: http://cdn.swapwink.com/images/dev/avatar/1_8883992123.jpg
     * 
     * getUrl('1_8883992123.jpg',ResourceImage::AVATAR, false, null)
     * Result: /images/dev/avatar_temp/1_8883992123.jpg?1471394390
     * 
     * En caso que el nombre este vacio, retornara la imagen por default.
     */
    
    public static function getUrl($name, $type, $options = []){
        
        $options = array_merge([
            'isPrimal' => true,
            'sizeImage' => self::SIZE_THUMB,
            'extraParams' => [
                'gender' => User::MAN
            ]
        ], $options);
        
        $resource = self::getResource($type, $options['sizeImage']);
        
        if(empty($name)){
            return self::getDefaultImage($type, $options['extraParams']);
        }
        
        if($options['isPrimal']){
            return self::getCDN() . self::getPath($resource, $name, true, false);
        }
        
        return DIRECTORY_SEPARATOR . self::getPath($resource, $name, false, false) . "?" . (new \DateTime())->getTimestamp();
        
    }
    
    /**
     * Obtener la url de la carita especificada.
     * 
     * @param string $faceType Tipo de carita.
     *
     * @return string
     */
    public static function getFaceUrlImage($faceType)
    {
        return Yii::$app->params['cdnPathThemesDefault'] . DIRECTORY_SEPARATOR . self::$_img . DIRECTORY_SEPARATOR . self::$_rating . DIRECTORY_SEPARATOR . $faceType . '.png';
    }

    /**
     * Obtener la imagen de la carita con el tag <img>.
     * 
     * @param string $faceType Tipo de carita
     * @param arr    $options  Opciones para la etiqueta img
     *
     * @return Html
     */
    public static function getFaceImage($faceType, $options = [])
    {
        return Html::img(self::getFaceUrlImage($faceType), $options);
    }

    /**
     * Obtiene el elemento imagen en html.
     *
     * @param string $name    Nombre del recurso
     * @param int    $type    Tipo del recurso
     * @param array  $options Opciones html
     *
     * @return string Devuelve el recurso en html image
     */
    public static function getHtmlImage($name, $type, $options = array('class' => 'user-avatar-mini'))
    {
        return Html::img(self::getUrl($name, $type), $options);
    }

    /**
     * Obtiene la imagen por defecto de acuerdo al tipo.
     * 
     * @param int $type Tipo de la imagen
     * @param arr $params Información adicional para generar la imagen por default.
     * Ejmplo:[
     *  "gender" => 1 
     * ];
     *
     * @return string ruta de la imagen por defecto
     * 
     * Exmaple: 
     * 
     * ResourceImage::getDefaultImage(ResourceImage::AVATAR, User::WOMAN)
     * Result : https://d224v694zcq7e8.cloudfront.net/images/avatar/default-woman.png
     */
    public static function getDefaultImage($type, $params = [])
    {
        self::setEnviroment();

        switch ($type) {
            case self::NOTIFICATION:
                if (!empty(Yii::$app->params['dir_notification_default'])) {
                    return Yii::$app->params['dir_notification_default'];
                }
                break;
            case self::LOGO:
                if (!empty(Yii::$app->params['dir_aff_default'])) {
                    return Yii::$app->params['dir_aff_default'];
                }
                break;
            case self::AVATAR:
                return self::getAvatarDefault($params);
            case self::COUPON:
                if (!empty(Yii::$app->params['dir_coupon_default'])) {
                    return Yii::$app->params['dir_coupon_default'];
                }
                break;
            default:
                return '';
        }

        $resource = self::getResource($type);

        return self::getCDN() . self::$_base . DIRECTORY_SEPARATOR . $resource . DIRECTORY_SEPARATOR . self::$_default;
    }
    
    /**
     * 
     * Obtener el avatar del usuario, se valida que exista el parametro
     * gender, de lo contrario retornara la imagen por default User::Man.
     * 
     * @param arr $params Parametros necesarios para definir la imagen por default.
     * Ejemplo:
     * [
     *  "gender" => 1 // Genero del usuario.
     * ]
     * @return string
     */
    public static function getAvatarDefault($params){
        
        return !empty($params['gender']) && $params['gender'] == User::WOMAN ? Yii::$app->params['dir_avatar_women'] : Yii::$app->params['dir_avatar_men'];
        
    }

    /**
     * Mueve una imagen de la carpeta temporal del servidor, a un
     * directorio temporal dentro del proyecto.
     * 
     * @param string       $name Nombre del elemento
     * @param UploadedFile $file Objeto imagen.
     * @param int          $type Tipo de elemento.
     * 
     * @return bool true | false Si se movio correctamente.
     */
    public static function uploadImageFile($name, $file, $type)
    {
        $target_path = self::getResourcePath($name, $type, false);

        $success = $file->saveAs($target_path);

        return (object) $response = [
            'success' => $success,
            'src' => $target_path,
        ];
    }

    /**
     * Realiza el crop al seleccionar una area
     * en especifico de la imagen.
     * 
     * @param string $src    Imagen origen.
     * @param string $dst    Imagen destino.
     * @param int    $width  El ancho para realizar el crop.
     * @param int    $height El alto para realizar el crop.
     * @param arr    $points x,y Puntos de referencia para realizar el crop, por default son 0 y 0.
     * @param arr    $box    width, heigth Ancho y alto para la redimension de la imagen, por defualt
     *                       son 200 y 200.
     * 
     * @return bool true | false En caso de haber generado la imagen correctamente.
     */
    public static function generateImageSquare($src, $dst, $width, $height, $points = [0, 0], $box = [200, 200])
    {
        try {
            Image::crop($src, $width, $height, [$points[0], $points[1]])
                    ->resize(new Box($box[0], $box[1]))
                    ->save($dst);
        } catch (Exception $exc) {
            Yii::error($exc->getTraceAsString());

            return false;
        }

        return true;
    }

    /**
     * Genera una imagen en miniatura.
     * 
     * @param string $src    Imagen origen.
     * @param string $dst    Imagen destino.
     * @param int    $width  Ancho.
     * @param int    $height Alto.
     * @param string $ext    Extension.
     * 
     * @return bool true | false  Si se genera la imagen en miniatura.
     */
    public static function generateImageThumnail($src, $dst, $width, $height, $ext)
    {
        try {
            Image::thumbnail($src, $width, $height)->save($dst, ['format' => $ext]);
        } catch (Exception $exc) {
            Yii::error($exc->getTraceAsString());

            return false;
        }

        return true;
    }

    /**
     * Guarda la imagen de la web al servidor.
     * 
     * @param string $url      Direccion de la imagen.
     * @param arr    $tempFile Variable referenciada que contiene todos los atributos de la imagen
     *                         descargada.
     * @param int    $type     Tipo de la imagen.
     *
     * @return true | false Si guarda correctamente la imagen o no.
     */
    public static function saveWebFile($url, &$tempFile, $type = self::AVATAR)
    {
        $uniqueId = uniqid();
        $name = $uniqueId . '.jpg';
        $TEMP_PATH = self::getDirectory($type, false);
        $finalPath = $TEMP_PATH . $name;

        $tempFile['extensionName'] = 'jpg';
        $tempFile['uniqueId'] = $uniqueId;
        $tempFile['name'] = $name;
        $tempFile['tempDirectory'] = $finalPath;

        $fp = fopen($finalPath, 'wb');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_exec($ch);

        curl_close($ch);
        fclose($fp);

        self::generateImageThumnail($finalPath, $finalPath, 200, 200, $tempFile['extensionName']);

        if (file_exists($finalPath)) {
            return true;
        }

        return false;
    }

    /**
     * Sube una imagen al servidor de amazon.
     * 
     * @param string $src               Ruta
     * @param string $name              Nombre del recurso
     * @param int    $type              Tipo de recurso
     * @param bool   $removeAfterUpload True si se desea remover la imagen temporal despues de subir.
     * @param bool   $sizeImage         Tamaño de la imagen.
     *
     * @return bool
     */
    public static function uploadFile($src, $name, $type, $removeAfterUpload = true, $sizeImage = self::SIZE_ORIGINAL)
    {
        $dst = self::getResourcePath($name, $type, true, false, $sizeImage);

        $factory = new OptimizerFactory();
        $optimizer = $factory->get();
        $optimizer->optimize($src);

        if (Yii::$app->storage->saveResource($dst, $src)) {
            if ($removeAfterUpload) {
                unlink($src);
            }

            return true;
        }

        return false;
    }

    /**
     * Subir original y thumb al servidor.
     * 
     * @param string $nameImage    Nombre de la imagen.
     * @param string $oldName      Nombre antiguo de la imagen.
     * @param arr    $sizeToCrop   Medidas para realizar el crop.
     * @param arr    $pointsToCrop Puntos de la selección del usuario donde iniciara el crop.
     * @param bool   $isResize     True si la imagen para el thumbnail sera redimensionada, False de lo contrario.
     * @param string $ext          Extension del archivo, en caso que isResize sea true.
     *
     * @return bool True si se subieron las imagenes correctamente, False de lo contrario.
     */
    public static function uploadImageWithThumb($type, $nameImage, $oldName, $sizeToCrop = null, $pointsToCrop = null, $isResize = false, $ext = null)
    {
        $src = self::getResourcePath($nameImage, $type, false);

        try {

            // Subiendo la imagen original sin procesar.
            if (!self::uploadFile($src, $nameImage, $type, false, false)) {
                throw new Exception(Yii::t('models/User', 'Cant upload original image.'));
            }

            if ($isResize) {
                if (!self::generateImageThumnail($src, $src, 200, 200, $ext)) {
                    throw new Exception('models/User', 'Cant resize the image');
                }
            } else {
                // Se procesa la imagen original generando el cuadrado con la selección del usuario.
                if (!self::generateImageSquare($src, $src, $sizeToCrop['width'], $sizeToCrop['height'], $pointsToCrop)) {
                    throw new Exception('models/User', 'Cant generate image square');
                }
            }

            //Subidendo la imagen generada y procesada al servidor.
            if (!self::uploadFile($src, $nameImage, $type, true, true)) {
                throw new Exception(Yii::t('models/User', 'Cant upload thumnail image.'));
            }

            // Se eliminan las imagenes anteriores (original, thumbnail) en caso de existir.
            if (!self::deleteImageWithThumb($oldName, $type)) {
                throw new Exception(Yii::t('models/User', 'Cant delete Avatar.'));
            }

            return true;
        } catch (Exception $exc) {
            Yii::error($exc);

            return false;
        }
    }

    /**
     * Eliminación del archivo (original y thumbnail).
     *
     * @param string $name Nombre del archivo
     *
     * @return bool True si se elimina correctamente, False de lo contrario.
     */
    public static function deleteImageWithThumb($name, $type)
    {
        return self::deleteFile($name, $type) && self::deleteFile($name, $type, true);
    }

    /**
     * Elimina una imagen al servidor de amazon.
     * 
     * @param string $name    Nombre del recurso
     * @param int    $type    Tipo de recurso
     * @param bool   $sizeImage Tamaño de la imagen.
     *
     * @return bool
     */
    public static function deleteFile($name, $type, $sizeImage = self::SIZE_ORIGINAL)
    {
        $dst = self::getResourcePath($name, $type, true, false, $sizeImage);

        if (Yii::$app->storage->deleteResource($dst)) {
            return true;
        }

        return false;
    }

    /**
     * Renombra una imagen del servidor de amazon.
     * 
     * @param string $name    Nombre del recurso
     * @param string $newName Nuevo nombre del recurso
     * @param int    $type    Tipo de recurso
     *
     * @return bool
     */
    public static function renameFile($name, $newName, $type)
    {
        $sourceName = self::getResourcePath($name, $type, true, false);
        $targetName = self::getResourcePath($newName, $type, true, false);

        if (Yii::$app->storage->copyResource($sourceName, $targetName)) {
            if (Yii::$app->storage->deleteResource($sourceName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Función que asigna el entorno en la que se esta trabajando
     * ya sea de produccion o de desarrollo.
     */
    private static function setEnviroment()
    {
        self::$_enviroment = YII_ENV;

        self::setBaseEnviroment();
    }

    /**
     * Función que asigna el basepath dependiendo del 
     * entorno en el que se este trabajando.
     */
    private static function setBaseEnviroment()
    {
        $basePath = '';

        switch (self::$_enviroment) {
            case self::ENVIROMENT_DEV:
                $basePath = self::BASE_PATH_DEV;
                break;
            case self::ENVIROMENT_TEST:
                $basePath = self::BASE_PATH_TEST;
                break;
            case self::ENVIROMENT_PROD:
                $basePath = self::BASE_PATH_PROD;
                break;
        }

        self::$_base = $basePath;
    }

    /**
     * Obtiene la ruta del recurso de acuerdo a los parametros solicitados: Carpeta y Nombre.
     *
     * @param string $folder      Carpeta de imagenes del recurso
     * @param string $name        Nombre del recurso
     * @param bool   $is_primal   1 si es el path principal, 0 si es path temporal. Por defecto es principal
     * @param bool   $is_absolute 1 si es ruta absoluta, 0  si es ruta relativa. Por defecto es absoluta
     *
     * @return string ruta del recurso
     */
    private static function getPath($folder, $name, $is_primal = true, $is_absolute = true)
    {
        return self::getContainer($folder, $is_primal, $is_absolute) . $name;
    }

    /**
     * Obtiene la ruta de la carpeta del recurso de acuerdo a los parametros solicitados: Tipo.
     * 
     * @param int  $type        Tipo de recurso
     * @param bool $is_primal   True si es el path principal, False si es path temporal. Por defecto es principal
     * @param bool $is_absolute True si es ruta absoluta, False si es ruta relativa. Por defecto es absoluta
     * @param bool $sizeImage   Tamaño de la imagen.
     * 
     * @return string ruta del recurso
     */
    public static function getUrlPath($type, $is_primal = true, $is_absolute = true, $sizeImage = self::SIZE_ORIGINAL)
    {
        $folder = self::getResource($type, $sizeImage);

        return self::getContainer($folder, $is_primal, $is_absolute);
    }

    /**
     * Obtiene la ruta del contenedor del recurso de acuerdo a la carpeta solicitada.
     *
     * @param string $folder      Carpeta de imagenes del recurso
     * @param bool   $is_primal   0 is es el path principal, 1 si es path temporal. Por defecto es principal
     * @param bool   $is_absolute 0 si es ruta relativa, 1 si es ruta absoluta. Por defecto es absoluta
     *
     * @return string ruta del contenedor
     */
    private static function getContainer($folder, $is_primal = true, $is_absolute = true)
    {
        self::setEnviroment();

        if ($is_absolute) {
            return Yii::getAlias('@' . self::$_enviroment . DIRECTORY_SEPARATOR . $folder . (($is_primal) ? '' : self::$_prefix . self::$_temp)) . DIRECTORY_SEPARATOR;
        }

        return self::$_base . DIRECTORY_SEPARATOR . $folder . (($is_primal) ? '' : self::$_prefix . self::$_temp) . DIRECTORY_SEPARATOR;
    }

    private static function getCDN()
    {
        /**
         * TODO: Changed for correct url in case cdn is missing.
         * Reported: Obed
         */
        return (isset(Yii::$app->params['cdn']) ? Yii::$app->params['cdn'] . "/" : "http://cdn.swapwink.com/");
    }

}
