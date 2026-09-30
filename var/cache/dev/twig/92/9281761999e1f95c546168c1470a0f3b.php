<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\MacroNamespace;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Sandbox\SecurityNotAllowedTestError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* @WebProfiler/Profiler/toolbar_item.html.twig */
class __TwigTemplate_d4266eac304f57f26794ab86a6e8ba29 extends Template
{
    private Source $source;
    /**
     * @var array<string, MacroNamespace>
     */
    private array $macros = [];
    private \Twig\Runtime\EscaperRuntime $escaper;

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->escaper = $env->getRuntime('Twig\Runtime\EscaperRuntime');

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "@WebProfiler/Profiler/toolbar_item.html.twig"));

        // line 1
        yield "<div class=\"sf-toolbar-block sf-toolbar-block-";
        yield (string) $this->escaper->escape(($context["name"] ?? null), "html", null, true);
        yield " sf-toolbar-status-";
        yield (string) $this->escaper->escape(((array_key_exists("status", $context)) ? (Twig\Extension\CoreExtension::default(($context["status"] ?? null), "normal")) : ("normal")), "html", null, true);
        yield " ";
        yield (string) $this->escaper->escape(((array_key_exists("additional_classes", $context)) ? (Twig\Extension\CoreExtension::default(($context["additional_classes"] ?? null), "")) : ("")), "html", null, true);
        yield "\" ";
        yield (string) ((array_key_exists("block_attrs", $context)) ? (Twig\Extension\CoreExtension::default(($context["block_attrs"] ?? null), "")) : (""));
        yield ">
    ";
        // line 2
        if (( !array_key_exists("link", $context) || (($tmp = ($context["link"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp))) {
            yield "<a href=\"";
            yield (string) $this->escaper->escape($this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getUrl("_profiler", ["token" => ($context["token"] ?? null), "panel" => ($context["name"] ?? null)]), "html", null, true);
            yield "\">";
        }
        // line 3
        yield "        <div class=\"sf-toolbar-icon\">";
        yield (string) $this->escaper->escape(((array_key_exists("icon", $context)) ? (Twig\Extension\CoreExtension::default(($context["icon"] ?? null), "")) : ("")), "html", null, true);
        yield "</div>
    ";
        // line 4
        if ((($tmp = ((array_key_exists("link", $context)) ? (Twig\Extension\CoreExtension::default(($context["link"] ?? null), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "</a>";
        }
        // line 5
        yield "        <div class=\"sf-toolbar-info\">";
        yield (string) $this->escaper->escape(((array_key_exists("text", $context)) ? (Twig\Extension\CoreExtension::default(($context["text"] ?? null), "")) : ("")), "html", null, true);
        yield "</div>
</div>
";
        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        return; yield;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "@WebProfiler/Profiler/toolbar_item.html.twig";
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
    public function getDefaultEscapeStrategy(): string|false
    {
        return "html";
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  76 => 5,  72 => 4,  67 => 3,  61 => 2,  50 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "@WebProfiler/Profiler/toolbar_item.html.twig", "/home/flavianj/Documents/htdocs/PHP/full-project/vendor/symfony/web-profiler-bundle/Resources/views/Profiler/toolbar_item.html.twig");
    }
}
