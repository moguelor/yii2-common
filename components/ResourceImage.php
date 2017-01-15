<?php

namespace jmoguelruiz\yii2\common\components;

use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use const YII_ENV;

/**
 * Expects:
 * 
 * * Habilitar componente en diferentes entornos.
 *   * Manejar tres entornos predefinidos.
 *      * dev, test, prod.
 *          * Si el usuario tiene mas enviroments modificar el ResourceImage local 
 *            para agregar las propiedades de cada enviroment faltantes.
 *   * Poder cambiar base paths de cada entorno.
 *   * Donde se guardarán las imagenes. (local o s3)
 * * Manejar tipos de recursos.
 *   * avatar, galeria, logo
 * * Guardar imagen en el servidor local.
 * * Guardar imagen en el servidor de amazon s3.
 * * Manejo de directorios temporales avatar_temp.
 * * Manejo de directorios finales avatar.
 * * Generar nombre para guardar el archivo.
 * * Procesar imagen antes de guardar, crop , resize etc.
 * * Guardar diferentes tamaños del archivo, original, thumb, etc.
 * * Obtener url del directorio temporal
 * * Obtener url del directorio final.
 * * Habilitar base_path donde se guardaran las imagenes.
 * * Guardar imagen desde una url.
 * * Subir la imagen a un directorio temporal para procesarla despues.
 * * Renombrar un archivo.
 * * Eliminar un archivo.
 * * Optimización de imagenes al subir.
 * 
 */

/**
 * USESS
 */
/**
 * <?php
    
$resourceImage = Yii::$app->resourceImage;

echo "-- Container -- <br/>";
echo "* Absolute <br/>";
echo $resourceImage->getAbsoluteDirectory();
echo "<br/> * CDN <br/>";
echo $resourceImage->getCDN();
echo "<br/>";
echo "<br/>";

echo "-- Enviroment -- <br/>";
echo $resourceImage->getBasePath();
echo "<br/>";
echo $resourceImage->getBasePath(ResourceImage::ENVIROMENT_PROD);
echo "<br/>";
echo "<br/>";

echo "-- Resource -- <br/>";
echo $resourceImage->getResource(ResourceImage::TYPE_TEAM_PHOTO);
echo "<br/>";
echo $resourceImage->getTempResource(ResourceImage::TYPE_TEAM_PHOTO);
echo "<br/> ";
echo "<br/>";

echo "-- Size -- <br/>";
echo $resourceImage->getSize(ResourceImage::SIZE_THUMB);
echo "<br/>";
echo "<br/>";

echo "-- URLs -- <br/>";
echo $resourceImage->generateRoot(['isWebUrl' => true])->getStringUrl();
echo "<br/>";
echo "<br/>";
echo $resourceImage->generateRoot()
                   ->generateBasePath(['enviroment' => 'test'])
                   ->getStringUrl();
echo "<br/>";
echo "<br/>";
echo $resourceImage->generateRoot()
                   ->generateBasePath()
                   ->generateResource(ResourceImage::TYPE_TEAM_PHOTO)
                   ->getStringUrl();
echo "<br/>";
echo "<br/>";
echo $resourceImage->generateRoot()
                   ->generateBasePath()
                   ->generateResource(ResourceImage::TYPE_TEAM_PHOTO)
                   ->generateSize(ResourceImage::SIZE_THUMB)
                   ->getStringUrl();
echo "<br/>";
echo "<br/>";
echo $resourceImage->generateRoot()
                   ->generateBasePath()
                   ->generateResource(ResourceImage::TYPE_TEAM_PHOTO)
                   ->generateSize(ResourceImage::SIZE_ORIGINAL)
                   ->generateName('ima-one.jpg')
                   ->getStringUrl();
?>
 */
class ResourceImage extends Component
{

    /**
     * Enviroments
     */
    const ENVIROMENT_DEV = 'dev';
    const ENVIROMENT_TEST = 'test';
    const ENVIROMENT_PROD = 'prod';

    /**
     * Servers to save final images.
     */
    const SERVER_LOCAL = 'local';
    const SERVER_S3 = 's3';

    /**
     * Sizes to images.
     */
    const SIZE_ORIGINAL = 'original';
    const SIZE_THUMB = 'thumb';

    /**
     * Base paths to configure in each enviroment.
     * Default: 
     * [
     *    'dev' => 'images/dev',
     *    'test' => 'images/test',
     *    'prod' => 'images'
     * ]
     * @var arr
     */
    public $basePaths = [];

    /**
     * Type of server, to save final images.
     * Default. SERVER_LOCAL.
     * @var string
     */
    public $serverType = self::SERVER_LOCAL;

    /**
     * Prefix temp.
     * @var type 
     */
    public $prefixTemp = 'temp';

    /**
     * Container base path;
     * @var type 
     */
    public $containerBasePath = "@frontend/web";

    /**
     * Url to cdn.
     * @var string 
     */
    public $cdn;

    /**
     * Concat elements neccesarys to complete the url.
     * @var string 
     */
    private $stringUrl;

    /**
     * init
     */
    public function init()
    {
        parent::init();

        $this->mergeBasePaths();
    }

    /**
     * Generate the name path.
     * 
     * @param string $name Name of the image.
     * @param arr $options Config to base path.
     * 
     * - No option yet.
     * 
     * Example:
     * [
     * ]
     * @return $this
     */
    public function generateName($name, $options = [])
    {
        $options = ArrayHelper::merge([
                        ], $options);

        $this->concatName($name);

        return $this;
    }

    /**
     * 
     * Generate the size path.
     * 
     * @param string $size Size of the image, thumb, original.
     * @param arr $options Config to base path.
     * 
     * - No option yet.
     * 
     * Example:
     * [
     * ]
     * @return $this
     */
    public function generateSize($size = null, $options = [])
    {
        $options = ArrayHelper::merge([
                        ], $options);

        $this->concatSize($size);

        return $this;
    }

    /**
     * 
     * Generate the resource path.
     * 
     * @param string $resource Path of resource of the image.
     * @param arr $options Config to base path.
     * 
     * isTemp - True to get temporal path of resource. Example. avatar_temp.
     *          False in reverse.
     * 
     * Example:
     * [
     *  'isTemp' => false 
     * ]
     * @return $this
     */
    public function generateResource($resource, $options = [])
    {

        $options = ArrayHelper::merge([
                    "isTemp" => false
                        ], $options);

        $options["isTemp"] ? $this->concatTempResource($resource) : $this->concatResource($resource);

        return $this;
    }

    /**
     * 
     * Generate the base path.
     * 
     * @params arr $options Config to base path.
     * 
     * enviroment - Enviroment running in the application.
     * 
     * Example:
     * [
     *  'enviroment' => 'dev' 
     * ]
     * @return $this
     */
    public function generateBasePath($options = [])
    {

        $options = ArrayHelper::merge([
                    'enviroment' => $this->getEnviromentRunning(),
                        ], $options);

        $this->concatBasePath($options['enviroment']);

        return $this;
    }

    /**
     * 
     * Generate the root path.
     * 
     * @param arr $options Config to root path.
     * 
     * isWebUrl - True to get cdn, False to get Absolute path.
     *            Default True.
     * 
     * Example:
     * [
     *  'isWebUrl' => true 
     * ]
     */
    public function generateRoot($options = [])
    {

        $options = ArrayHelper::merge([
                    'isWebUrl' => true
                        ], $options);

        $options['isWebUrl'] ? $this->concatCDN() : $this->concatAbsoluteDirectory();

        return $this;
    }

    /**
     * Get the directory absolute depending of
     * enviroment.
     * @return type
     */
    public function getAbsoluteDirectory()
    {
        return Yii::getAlias($this->containerBasePath);
    }

    /**
     * Get the cdn url.
     * @return type
     */
    public function getCDN()
    {
        return $this->cdn;
    }

    /**
     * Get the base path by enviroment specified, if is null get the
     * enviroment running in application.
     * @param string $enviroment Enviroment.
     * @return string 
     */
    public function getBasePath($enviroment = null)
    {

        if (empty($enviroment)) {
            $enviroment = $this->getEnviromentRunning();
        }

        if (!empty($this->basePaths[$enviroment])) {
            return $this->basePaths[$enviroment];
        }
    }

    /**
     * Get the resource specified.
     * @param string $type Type of resource.
     */
    public function getResource($type)
    {

        $resources = $this->resources();

        if (!empty($resources[$type])) {
            return $resources[$type];
        }
    }

    /**
     * Get temporal folder to resource.
     * @param type $type
     */
    public function getTempResource($type)
    {
        return $this->getResource($type) . "_" . $this->prefixTemp;
    }

    /**
     * Get the size specified.
     * @param int $size Type of size;
     * @return string
     */
    public function getSize($size)
    {

        $sizes = $this->sizes();

        if (!empty($sizes[$size])) {
            return $sizes[$size];
        }
    }

    /**
     * Get Name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the enviroment running.
     * @return string
     */
    public function getEnviromentRunning()
    {
        return YII_ENV;
    }

    /**
     * Add size to stringUrl
     * @return $this
     */
    public function concatAbsoluteDirectory()
    {
        $this->setStringUrl($this->getAbsoluteDirectory());
        return $this;
    }

    /**
     * Add cdn to stringUrl
     * @return $this
     */
    public function concatCDN()
    {
        $this->setStringUrl($this->getCDN());
        return $this;
    }

    /**
     * Add base path to stringUrl
     * @return $this
     */
    public function concatBasePath($enviroment = null)
    {
        $this->setStringUrl($this->getBasePath($enviroment));
        return $this;
    }

    /**
     * Add size to stringUrl
     * @return $this
     */
    public function concatResource($type)
    {
        $this->setStringUrl($this->getResource($type));
        return $this;
    }

    /**
     * Add tempResourcer to stringUrl
     * @return $this
     */
    public function concatTempResource($type)
    {
        $this->setStringUrl($this->getTempResource($type));
        return $this;
    }

    /**
     * Add size to stringUrl
     * @return $this
     */
    public function concatSize($size = self::SIZE_ORIGINAL)
    {
        if (!empty($size) && $size != self::SIZE_ORIGINAL) {
            $this->setStringUrl($this->getSize($size));
        }

        return $this;
    }

    public function concatName($name)
    {
        $this->setStringUrl($name, "");
    }

    /**
     * Get string url
     * @return string
     */
    public function getStringUrl()
    {
        $stringUrl = $this->stringUrl;
        $this->clearStringUrl();
        return $stringUrl;
    }

    /**
     * Set string url
     * @param string $path Path to concatenate.
     * @param string $separator Separator.
     */
    public function setStringUrl($path, $separator = DIRECTORY_SEPARATOR)
    {

        $this->stringUrl .= $path . $separator;
    }

    /**
     * Set name.
     * @param string $name Name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Resources common override in each module.
     * @return arr
     */
    public function resources()
    {
        return [];
    }

    /**
     * Sizes of the system.
     * @return string
     */
    public function sizes()
    {

        return [
            self::SIZE_ORIGINAL => '',
            self::SIZE_THUMB => 'thumb'
        ];
    }

    /**
     * Clear property stringUrl
     */
    public function clearStringUrl()
    {
        $this->stringUrl = "";
    }

    /**
     * Integrate the basePaths defaults with the user basepaths;
     */
    private function mergeBasePaths()
    {
        $this->basePaths = ArrayHelper::merge([
                    'dev' => 'images/dev',
                    'test' => 'images/test',
                    'prod' => 'images'
                        ], $this->basePaths);
    }

}
