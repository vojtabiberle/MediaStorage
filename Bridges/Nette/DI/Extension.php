<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\DI;

use Nette\DI\Statement;
use Nette\Utils\Strings;
use vojtabiberle\MediaStorage\Exceptions\Exception;
use Nette;
use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

class Extension extends CompilerExtension
{
    private $defaults = [
        'wwwDir' => '%wwwDir%',
        'appDir' => '%appDir%',
        'mediaStoragePath' => 'mediastorage',
        'disableMultiupload' => false,
        'uploadFieldLabel' => 'Upload:',
        'formClass' => 'Nette\Application\UI\Form',
        'fileClass' => 'vojtabiberle\MediaStorage\Files\File',
        'imageClass' => 'vojtabiberle\MediaStorage\Images\Image',
        'imageManager' => [
            'noimage' => 'noimage/noimage.png',
            'allowed' => [],
            'helpers' => [
                'crop' => 'vojtabiberle\MediaStorage\Images\Helpers\Crop',
                'sharpen' => 'vojtabiberle\MediaStorage\Images\Helpers\Sharpen',
                'quality' => 'vojtabiberle\MediaStorage\Images\Helpers\Quality'
            ],
            'sizes' => [
                'grid-thumbnail' => [
                    'width' => 200,
                    'height' => 200,
                    'flag' => 'fit'
                ],
                'trip' => [
                    'width' => 255,
                    'height' => 390,
                    'flag' => 'fit'
                ],
                'experience-detail-thumbnail' => [
                    'width' => 242,
                    'height' => 157,
                    'flag' => 'fit'
                ]
            ]
        ],
        'fileManager' => [
            'allowed' => [],
            'blacklist' => [
                'php',
            ],
        ],
        'router' => [
            'imagesMask' => 'media/images[/<namespace ((?!size|filter|noimage).)+>][/<size size-[a-zA-Z0-9-_:;,]+>][/<filter filter-[a-zA-Z0-9-_:;,]+>][/<noimage noimage-[a-zA-Z0-9-_:\.]+>]/<name>',
            'filesMask' => 'media/files[/<namespace .+>]/<name>',
            'iconsMask' => 'media/icons[/<size size-[a-zA-Z0-9-_:;,]+>]/<name>',
            'adminMask' => 'admin/media/<action=grid>[/<id>]',
            'popupAction' => 'admin/media/popup/show',
            'deleteAction' => 'admin/media/delete',
            'resize' => true,
            'flag' => 0,
            'noimage' => true,
            'disable' => false,
        ],
    ];

    public function loadConfiguration()
    {
        $config = $this->getSettings();
        $builder = $this->getContainerBuilder();

        /**
         * Same starting point is basic point, where app lives.
         */
        $config['baseDir'] = Strings::findPrefix([$config['wwwDir'], $config['appDir']]);


        /*$this->compiler->parseServices($builder, $this->loadFromFile(__DIR__ . DIRECTORY_SEPARATOR . 'services.neon'), $this->name);*/

        $builder->addDefinition($this->prefix('uploadFormFactory'))
            ->setFactory('vojtabiberle\MediaStorage\Bridges\Nette\Forms\UploadFormFactory')
            ->setClass('vojtabiberle\MediaStorage\Bridges\Nette\Forms\UploadFormFactory')
            ->setArguments([$config['formClass'], $config['uploadFieldLabel']]);

        $builder->addDefinition($this->prefix('multiuploadFormFactory'))
            ->setFactory('vojtabiberle\MediaStorage\Bridges\Nette\Forms\MultiuploadFormFactory')
            ->setClass('vojtabiberle\MediaStorage\Bridges\Nette\Forms\MultiuploadFormFactory')
            ->setArguments([$config['formClass'], $config['uploadFieldLabel']]);

        $builder->addDefinition($this->prefix('filterFormFactory'))
            ->setFactory('vojtabiberle\MediaStorage\Bridges\Nette\Forms\FilterFormFactory')
            ->setClass('vojtabiberle\MediaStorage\Bridges\Nette\Forms\FilterFormFactory')
            ->setArguments([$config['formClass'], 'Query:']);

        $builder->addDefinition($this->prefix('cardMediaViewControl'))
            ->setClass('vojtabiberle\MediaStorage\Bridges\Nette\Controls\CardView\CardViewControl');

        $builder->addDefinition($this->prefix('gridMediaViewControlFactory'))
            ->setFactory('vojtabiberle\MediaStorage\Bridges\Nette\Controls\GridMediaView\GridMediaViewControlFactory');

        $builder->addDefinition($this->prefix('uploadControlFactory'))
            ->setFactory('vojtabiberle\MediaStorage\Bridges\Nette\Controls\Upload\UploadControlFactory')
            ->setArguments(['disableMultiupload' => $config['disableMultiupload']]);

        $builder->addDefinition($this->prefix('ajaxUploadControlFactory'))
            ->setFactory('vojtabiberle\MediaStorage\Bridges\Nette\Controls\AjaxUpload\AjaxUploadControlFactory')
            ->setArguments(['disableMultiupload' => $config['disableMultiupload']]);

        $builder->addDefinition($this->prefix('manager'))
            ->setClass('vojtabiberle\MediaStorage\Manager')
            ->setArguments(['config' => $config]);

        $builder->addDefinition($this->prefix('storage'))
            ->setClass('vojtabiberle\MediaStorage\Storage')
            ->setArguments([
                'mediaStoragePath' => $config['baseDir'] . DIRECTORY_SEPARATOR . $config['mediaStoragePath'],
                'fileClass' => $config['fileClass'],
                'imageClass' => $config['imageClass'],
            ]);

        if (0 === count($builder->findByType('vojtabiberle\MediaStorage\Bridges\IFilesystemStorage'))) {
            $builder->addDefinition($this->prefix('filesystemStorage'))
                ->setClass('vojtabiberle\MediaStorage\Bridges\FilesystemStorage');
        }

        if (0 === count($builder->findByType('vojtabiberle\MediaStorage\Bridges\IDatabaseStorage'))) {
            $builder->addDefinition($this->prefix('databaseStorage'))
                ->setClass('vojtabiberle\MediaStorage\Bridges\Nette\Model\MediaStorage');
        }

        if ($config['router']['disable'] === false) {
            $builder->addDefinition($this->prefix('routerFactory'))
                ->setFactory('vojtabiberle\MediaStorage\Bridges\Nette\Router\Factory::createRouter')
                ->setClass('Nette\Application\IRouter')
                ->setArguments([$config['router']])
                ->setAutowired(false);

            $builder->addDefinition($this->prefix('mediaPresenter'))
                ->setClass('vojtabiberle\MediaStorage\Bridges\Nette\Presenters\MediaPresenter');
        }

        $builder->addDefinition($this->prefix('mediaPopupPresenter'))
            ->setClass('vojtabiberle\MediaStorage\Bridges\Nette\Presenters\MediaPopupPresenter');
    }

    /**
     * @return array
     */
    public function getSettings() {
        if (method_exists($this, 'validateConfig')) {
            $config = $this->validateConfig($this->defaults, $this->config);
            $config['wwwDir'] = Nette\DI\Helpers::expand($config['wwwDir'], $this->getContainerBuilder()->parameters);
            $config['appDir'] = Nette\DI\Helpers::expand($config['appDir'], $this->getContainerBuilder()->parameters);
        } else {
            $config = $this->getConfig($this->defaults); // deprecated
        }
        return $config;
    }

    public function beforeCompile() {
        $builder = $this->getContainerBuilder();
        $config = $this->getSettings();

        $builder->getDefinition('nette.latteFactory')
            ->addSetup('vojtabiberle\MediaStorage\Bridges\Latte\Macros::install(?->getCompiler())', ['@self']);

        if ($config['router']['disable'] === false) {

            $builder->getDefinition('router')
                ->addSetup('vojtabiberle\MediaStorage\Bridges\Nette\Router\Factory::prepend($service, ?)', [$this->prefix('@routerFactory')]);

            $builder->getDefinition('nette.presenterFactory')
                ->addSetup('setMapping', [
                    ['MediaStorage' => 'vojtabiberle\MediaStorage\Bridges\Nette\Presenters\*Presenter']
                ]);
        }
    }

    public function afterCompile(Nette\PhpGenerator\ClassType $class) {
        $methods = $class->getMethods();
        $init = $methods['initialize'];

        $init->addBody('vojtabiberle\MediaStorage\Bridges\Nette\Forms\Controls\SingleSelectFileChoicer::register();');
        $init->addBody('vojtabiberle\MediaStorage\Bridges\Nette\Forms\Controls\MultiSelectFileChoicer::register();');
    }
}