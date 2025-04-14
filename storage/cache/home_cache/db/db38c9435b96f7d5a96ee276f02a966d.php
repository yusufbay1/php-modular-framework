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

/* layout.twig */
class __TwigTemplate_a7bd9f59f69d276835a7af32eaf3aa45 extends Template
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

        $this->parent = false;

        $this->blocks = [
            'css' => [$this, 'block_css'],
            'content' => [$this, 'block_content'],
            'after_script' => [$this, 'block_after_script'],
            'script' => [$this, 'block_script'],
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 1
        yield "<!DOCTYPE html>
<html lang=\"tr\">
<head>
    <base href=\"";
        // line 4
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Core\Functions\IFunction::base(), "html", null, true);
        yield "\"/>
    <title>";
        // line 5
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["title"] ?? null), "html", null, true);
        yield "</title>
    ";
        // line 6
        yield from $this->loadTemplate("layout/head.twig", "layout.twig", 6)->unwrap()->yield($context);
        // line 7
        yield "    ";
        yield from $this->unwrap()->yieldBlock('css', $context, $blocks);
        // line 8
        yield "</head>
<body>
<main>
    ";
        // line 11
        yield from $this->unwrap()->yieldBlock('content', $context, $blocks);
        // line 12
        yield "</main>
";
        // line 13
        yield from $this->unwrap()->yieldBlock('after_script', $context, $blocks);
        // line 14
        yield from $this->loadTemplate("layout/footer.twig", "layout.twig", 14)->unwrap()->yield($context);
        // line 15
        yield from $this->unwrap()->yieldBlock('script', $context, $blocks);
        // line 16
        yield "</body>
</html>";
        yield from [];
    }

    // line 7
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_css(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield from [];
    }

    // line 11
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield from [];
    }

    // line 13
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_after_script(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield from [];
    }

    // line 15
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_script(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "layout.twig";
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
        return array (  116 => 15,  106 => 13,  96 => 11,  86 => 7,  80 => 16,  78 => 15,  76 => 14,  74 => 13,  71 => 12,  69 => 11,  64 => 8,  61 => 7,  59 => 6,  55 => 5,  51 => 4,  46 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "layout.twig", "C:\\inetpub\\wwwroot\\iycorgv2\\resources\\layout.twig");
    }
}
