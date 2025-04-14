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

/* hello.twig */
class __TwigTemplate_48d0ee3d5e8bfbcc4abca88869e8fb19 extends Template
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
        $this->parent = $this->loadTemplate("layout.twig", "hello.twig", 1);
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
        yield "    ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["name"] ?? null), "html", null, true);
        yield "
    ";
        // line 5
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["id"] ?? null), "html", null, true);
        yield "
    <div style=\"min-height: 100vh; background: #f1f1f1; display: flex; align-items: center; justify-content: center;text-align: center\">
        <div style=\"max-width: 500px; margin: 0 auto;\">
            <img src=\"";
        // line 8
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Core\Functions\IFunction::assets("images/modular.png"), "html", null, true);
        yield "\" alt=\"Logo\" style=\"width: 100%; margin-bottom: 2rem;border:1px solid #d6d6d6\" class=\"rotating-logo\">
            <p style=\"font-size: 1.2rem; color: #555; margin-bottom: 2rem;\">
                PHP yapınıza hoş geldiniz. Bu, PHP ile geliştirilmiş bir framework'tür.
            </p>
            <a href=\"/docs\" style=\"padding: 0.75rem 1.5rem; font-size: 1rem; background: #1a1a1a; color: white; border-radius: 6px; text-decoration: none; transition: background 0.3s ease;\">
                Belgeleri Görüntüle
            </a>
        </div>
    </div>

    <style>
        body{
            margin: 0;
            padding: 0;
            overflow: hidden;
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
        return "hello.twig";
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
        return array (  69 => 8,  63 => 5,  58 => 4,  51 => 3,  40 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "hello.twig", "C:\\inetpub\\wwwroot\\modular_freamework\\app\\Modules\\Home\\views\\hello.twig");
    }
}
