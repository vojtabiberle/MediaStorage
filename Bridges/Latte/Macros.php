<?php

namespace vojtabiberle\MediaStorage\Bridges\Latte;

use Latte;
use Latte\Compiler;
use Latte\Macros\MacroSet;

class Macros extends MacroSet
{
    /**
     * @param Compiler $compiler
     */
    public static function install(Compiler $compiler)
    {
        $me = new static($compiler);

        $me->addMacro('img', [$me, 'beginImg'], null, [$me, 'attrImg']);
        $me->addMacro('imgLink', [$me, 'beginLink'], null, [$me, 'attrLink']);
        $me->addMacro('fileIcon', [$me, 'beginFileIcon'], null, [$me, 'attrFileIcon']);
        $me->addMacro('file', [$me, 'beginFile'], null, [$me, 'attrFile']);

        $me->addMacro('showSingleMedia', [$me, 'beginShowSingleMedia'], null, [$me, 'attrShowSingleMedia']);
        $me->addMacro('showMultipleMedia', '', [$me, 'endShowMultiMedia']);
        $me->addMacro('showPrimaryMedia', [$me, 'beginShowPrimaryMedia'], null, [$me, 'attrShowPrimaryMedia']);
    }

    /**
     * @param Latte\MacroNode $node
     * @param Latte\PhpWriter $writer
     * @return string
     */
    public function attrLink(Latte\MacroNode $node, Latte\PhpWriter $writer) {
        if ($node->htmlNode->name === 'a') {
            $attr = 'href=';
        } else {
            $attr = 'src=';
        }

        $body = <<<'BODY'
echo ' $ATTR$"';
echo %escape($_presenter->link(":MediaStorage:Images:", %node.array?));
echo '"';
BODY;

        $body = str_replace('$ATTR$', $attr, $body);
        return $writer->write($body);
    }

    /**
     * @param Latte\MacroNode $node
     * @param Latte\PhpWriter $writer
     * @return string
     */
    public function beginImg(Latte\MacroNode $node, Latte\PhpWriter $writer) {
        $body =<<<'BODY'
echo %escape($_presenter->link(":MediaStorage:Images:", %node.array?));
BODY;

        return $writer->write($body);
    }

    /**
     * @param Latte\MacroNode $node
     * @param Latte\PhpWriter $writer
     * @return string
     */
    public function attrImg(Latte\MacroNode $node, Latte\PhpWriter $writer) {
        if ($node->htmlNode->name === 'a') {
            $attr = 'href=';
        } else {
            $attr = 'src=';
        }

        $body =<<<'BODY'
echo ' $ATTR$"';
echo %escape($_presenter->link(":MediaStorage:Images:", %node.array?));
echo '"';
BODY;

        $body = str_replace('$ATTR$', $attr, $body);
        return $writer->write($body);
    }

    public function beginFileIcon(Latte\MacroNode $node, Latte\PhpWriter $writer)
    {
        $body =<<<'BODY'
echo %escape($_presenter->link(":MediaStorage:Icons:", %node.array?));
BODY;

        return $writer->write($body);
    }

    public function attrFileIcon(Latte\MacroNode $node, Latte\PhpWriter $writer)
    {
        if ($node->htmlNode->name === 'a') {
            $attr = 'href=';
        } else {
            $attr = 'src=';
        }

        $body =<<<'BODY'
echo ' $ATTR$"';
echo %escape($_presenter->link(":MediaStorage:Icons:", %node.array?));
echo '"';
BODY;

        $body = str_replace('$ATTR$', $attr, $body);
        return $writer->write($body);
    }

    public function beginFile(Latte\MacroNode $node, Latte\PhpWriter $writer)
    {
        $body =<<<'BODY'
echo %escape($_presenter->link(":MediaStorage:Files:", %node.array?));
BODY;

        return $writer->write($body);
    }

    public function attrFile(Latte\MacroNode $node, Latte\PhpWriter $writer)
    {
        if ($node->htmlNode->name === 'a') {
            $attr = 'href=';
        } else {
            $attr = 'src=';
        }

        $body = <<<'BODY'
echo ' $ATTR$"';
$__attr = %node.array;
$name = $__attr['name'];
echo %escape($_presenter->link(":MediaStorage:Files:", ['name' => $name]));
echo '"';
BODY;

        $body = str_replace('$ATTR$', $attr, $body);
        return $writer->write($body);
    }

    /**
     * @param Latte\MacroNode $node
     * @param Latte\PhpWriter $writer
     * @return string
     */
    public function beginShowSingleMedia(Latte\MacroNode $node, Latte\PhpWriter $writer)
    {
        $body = <<<'BODY'
try {
    $__file = $mediaManager->find(
        \vojtabiberle\MediaStorage\FileFilter::create()->getByNamespace(%node.word)
    );

    if ($__file->isImage()) {
        echo %escape($_presenter->link(":MediaStorage:Images:", array_merge(['name' => $__file, 'namespace' => %node.word], %node.array)));
    } else {
        echo %escape($_presenter->link(":MediaStorage:Files:", array_merge(['name' => $__file, 'namespace' => %node.word], %node.array)));
    }
} catch(\vojtabiberle\MediaStorage\Exceptions\FileNotFoundException $e) {
    echo $e->getMessage();
    echo '" ';

    echo 'data-mediastorage-error="';
    echo $e->getMessage();
}
BODY;

        return $writer->write($body);
    }

    /**
     * @param Latte\MacroNode $node
     * @param Latte\PhpWriter $writer
     * @return string
     */
    public function attrShowSingleMedia(Latte\MacroNode $node, Latte\PhpWriter $writer)
    {
        if ($node->htmlNode->name === 'a') {
            $attr = 'href=';
        } else {
            $attr = 'src=';
        }

        $body = <<<'BODY'
try {
    $__file = $mediaManager->find(
        \vojtabiberle\MediaStorage\FileFilter::create()->getByNamespace(%node.word)
    );

    echo ' $ATTR$"';
    if ($__file->isImage()) {
        echo %escape($_presenter->link(":MediaStorage:Images:", array_merge(['name' => $__file, 'namespace' => %node.word], %node.array)));
    } else {
        echo %escape($_presenter->link(":MediaStorage:Files:", array_merge(['name' => $__file, 'namespace' => %node.word], %node.array)));
    }
    echo '"';
} catch(\vojtabiberle\MediaStorage\Exceptions\FileNotFoundException $e) {
    echo ' $ATTR$"';
    echo $e->getMessage();
    echo '" ';

    echo 'data-mediastorage-error="';
    echo $e->getMessage();
    echo '"';
}
BODY;

        $body = str_replace('$ATTR$', $attr, $body);
        return $writer->write($body);
    }

    /**
     * @param Latte\MacroNode $node
     * @param Latte\PhpWriter $writer
     */
    public function endShowMultiMedia(Latte\MacroNode $node, Latte\PhpWriter $writer)
    {
        $opening = <<<'OPENING'
<?php
try {
    $__args = %node.word;
    if (preg_match('#(?P<namespace>.*)\s+as\s+\$(?P<variable>.*)#i', $__args, $matches)) {
        $namespace = $matches['namespace'];
        $variable = $matches['variable'];
    } else {
        $namespace = $__args;
        $variable = '__file';
    }

    $__files = $mediaManager->find(
        \vojtabiberle\MediaStorage\FileFilter::create()->findByNamespace($namespace)
    );

    $__args_array = %node.array;

    $iterations = 0;
    foreach ($__files as $$variable):
    ?>
OPENING;

        if ($node->htmlNode instanceof Latte\HtmlNode) { //inline n:macro inside tag - we need to process tag manualy
            $content = $node->content;
            preg_match('#.+(?P<attrCode>n:[0-9]+)[^0-9]+#iU', $content, $matches);

            $tag = $node->htmlNode->name;

            $link = <<<'PARAMS'
    <?php
    $__params = $__args_array;
    $__params['name'] = $$variable;
PARAMS;

            if ($tag === 'a') {
                $link .= <<<'LINK'
    echo 'href="';
    if ($$variable->isImage()) {
        echo Latte\Runtime\Filters::escapeHtml($_presenter->link(":MediaStorage:Images:", $__params));
    } else {
        $__params = [];
        $__params['name'] = $$variable;
        echo Latte\Runtime\Filters::escapeHtml($_presenter->link(":MediaStorage:Files:", $__params));
    }
    echo '"';
    ?>
LINK;
            } else {
                $link .= <<<'LINK'
    echo 'src="';
    if ($$variable->isImage()) {
        echo Latte\Runtime\Filters::escapeHtml($_presenter->link(":MediaStorage:Images:", $__params));
    } else {
        $__params['name'] = $$variable->getIconName();
        echo Latte\Runtime\Filters::escapeHtml($_presenter->link(":MediaStorage:Icons:", $__params));
    }
    echo '"';
    ?>
LINK;
            }

            /*$link .= <<<'LINK'
if ($$variable->isImage()) {
    echo Latte\Runtime\Filters::escapeHtml($_presenter->link(":MediaStorage:Images:", $__params));
} else {
    $__params['name'] = $$variable->getIconName();
    echo Latte\Runtime\Filters::escapeHtml($_presenter->link(":MediaStorage:Icons:", $__params));
}
echo '"';
?>
LINK;*/
            if ( isset($matches['attrCode'])) {
                $attrCode = $matches['attrCode'];
                $content = str_replace([$attrCode], [$link], $content);
            }

            $node->content = $content;
        }

        $closing = <<<'CLOSING'
    <?php
    $iterations++;
    endforeach;
} catch(\vojtabiberle\MediaStorage\Exceptions\FileNotFoundException $e) {
    echo '<div data-mediastorage-error="';
    echo $e->getMessage();
    echo '"></div>';
}
?>
CLOSING;

        $node->openingCode = $writer->write($opening);
        $node->closingCode = $writer->write($closing);
    }

    /**
     * @param Latte\MacroNode $node
     * @param Latte\PhpWriter $writer
     * @return string
     */
    public function beginShowPrimaryMedia(Latte\MacroNode $node, Latte\PhpWriter $writer)
    {
        $body = <<<'BODY'
try {
    $__file = $mediaManager->find(
        \vojtabiberle\MediaStorage\FileFilter::create()->getByNamespace(%node.word)->addAdditionalCondition('media_usage', 'primary = 1')
    );

    if ($__file->isImage()) {
        echo %escape($_presenter->link(":MediaStorage:Images:", array_merge(['name' => $__file, 'namespace' => %node.word], %node.array)));
    } else {
        echo %escape($_presenter->link(":MediaStorage:Files:", array_merge(['name' => $__file, 'namespace' => %node.word], %node.array)));
    }
} catch(\vojtabiberle\MediaStorage\Exceptions\FileNotFoundException $e) {
    echo $e->getMessage();
    echo '" ';

    echo 'data-mediastorage-error="';
    echo $e->getMessage();
}
BODY;

        return $writer->write($body);
    }

    /**
     * @param Latte\MacroNode $node
     * @param Latte\PhpWriter $writer
     * @return string
     */
    public function attrShowPrimaryMedia(Latte\MacroNode $node, Latte\PhpWriter $writer)
    {
        if ($node->htmlNode->name === 'a') {
            $attr = 'href=';
        } else {
            $attr = 'src=';
        }

        $body = <<<'BODY'
try {
    $__file = $mediaManager->find(
        \vojtabiberle\MediaStorage\FileFilter::create()->getByNamespace(%node.word)->addAdditionalCondition('media_usage', 'primary = 1')
    );

    echo ' $ATTR$"';
    if ($__file->isImage()) {
        echo %escape($_presenter->link(":MediaStorage:Images:", array_merge(['name' => $__file, 'namespace' => %node.word], %node.array)));
    } else {
        echo %escape($_presenter->link(":MediaStorage:Files:", array_merge(['name' => $__file, 'namespace' => %node.word], %node.array)));
    }
    echo '"';
} catch(\vojtabiberle\MediaStorage\Exceptions\FileNotFoundException $e) {
    echo ' $ATTR$"';
    echo $e->getMessage();
    echo '" ';

    echo 'data-mediastorage-error="';
    echo $e->getMessage();
    echo '"';
}
BODY;

        $body = str_replace('$ATTR$', $attr, $body);
        return $writer->write($body);
    }
}