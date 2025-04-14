<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* index.twig */
class __TwigTemplate_7dcd59a843572cb041f2d35c67d83a5f extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->blocks = [
            'content' => [$this, 'block_content'],
        ];
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 1
        return "layout.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $this->parent = $this->loadTemplate("layout.twig", "index.twig", 1);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 4
        yield "    <div style=\"min-height: 100vh; background: #000; display: flex; align-items: center; justify-content: center;text-align: center\">
        <div style=\"max-width: 500px; margin: 0 auto;\">
            <img src=\"";
        // line 6
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Core\Functions\IFunction::assets("images/modular.png"), "html", null, true);
        yield "\" alt=\"Logo\" style=\"width: 100%;\" class=\"rotating-logo\"/>
            <h1 style=\"font-size: 2.5rem; color: #333; margin-bottom: 1rem;margin-top: 0;\"  class=\"shimmer\">Welcome to Modular PHP</h1>
            <p style=\"font-size: 1.2rem; color: #666; margin-bottom: 2rem;margin-top: 0;\">
                A modular PHP framework for building scalable applications.
                <br>
                This is a demo page to showcase the framework's capabilities.
            </p>
            <a href=\"/docs\" style=\"padding: 0.75rem 1.5rem; font-size: 1rem; background: #1a1a1a; color: white; border-radius: 6px; text-decoration: none; transition: background 0.3s ease;\">
                Documentation
            </a>
        </div>
    </div>
    <style>
        body{
            margin: 0;
            padding: 0;
            overflow: hidden;
            background: #000;
        }
        .rotating-logo{
            animation: slow-rotate 20s linear infinite;
            display: block;
            margin: auto;
        }

        .shimmer{
            display: inline-block;
            position: relative;
            background: linear-gradient(to right,
            #444 0%,
            #444 40%,
            #fff 50%,
            #444 60%,
            #444 100%);
            background-size: 200% 100%;
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            animation: shimmerFlat 2s infinite;
        }

        @keyframes shimmerFlat {
            0% {
                background-position: 200% 0;
            }
        }

        @keyframes slow-rotate {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
    </style>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "index.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  62 => 6,  58 => 4,  51 => 3,  40 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "index.twig", "C:\\inetpub\\wwwroot\\iycorgv2\\app\\Modules\\Home\\views\\index.twig");
    }
}
