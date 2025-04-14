<?php

namespace App\Http\Controllers;

use Core\Database\Env;
use Core\Functions\CustomFunctionsExtension;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

abstract class Controller
{
    protected string $className;
    protected bool $moduleKey = false;
    protected mixed $cacheAdapter;
    protected bool $devMode;

    public function __construct()
    {
        $this->devMode = Env::get('DEV_MODE');
        $this->className = get_class($this);
    }

    public function getModuleName(): ?string
    {
        $parts = explode('\\', $this->className);
        $moduleKey = array_search('Modules', $parts);
        return $parts[$moduleKey + 1] . ($this->moduleKey ? '/' . $parts[3] : '') ?? null;
    }

    public function view($file, $data = [], $reload = true): string
    {
        $moduleName = $this->getModuleName();
        $moduleViewPath = 'app/Modules/' . $moduleName . '/views';
        $globalViewPath = './resources';
        $loader = new FilesystemLoader([$moduleViewPath, $globalViewPath]);
        $cacheDir =  'storage/cache/' . strtolower($moduleName) . '_cache';

        $this->cacheAdapter = new FilesystemAdapter('cache', 0, $cacheDir);

        $twig = new Environment($loader, [
            'cache' => $reload ? $cacheDir : $reload,
            'auto_reload' => $this->devMode,
        ]);

        $twig->addExtension(new CustomFunctionsExtension());

        $cacheKey = md5($_SERVER['REQUEST_URI'] . $file);
        $cacheItem = $this->cacheAdapter->getItem($cacheKey);
        $renderedTemplate =(!$this->devMode && $cacheItem->isHit() ? $cacheItem->get() : null);

        if (!$renderedTemplate) {
            $template = $twig->load($file . '.twig');
            $renderedTemplate = $template->render(array_merge($this->data(), $data));
            $cacheItem->set($renderedTemplate);
            $this->cacheAdapter->save($cacheItem);
        }
        return $renderedTemplate;
    }

    protected function data(): array
    {
        return [];
    }
}