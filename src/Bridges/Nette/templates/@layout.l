<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

        <link rel="icon" href="{$basePath}/favicon.ico" type="image/x-icon" />
        <link rel="apple-touch-icon" href="{$basePath}/favicon.ico" />
        <link rel="apple-touch-icon-precomposed" href="{$basePath}/favicon.ico" />

        <!--[if lt IE 9]>
            <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

        {ifset #title}<title>{include title|striptags}</title>{/ifset}

        {block scripts}{/block}

        {block head}{/block}
    </head>

    <body>

        <div class="container-fluid">

            <div class="row row-offcanvas row-offcanvas-left">

                <div class="col-sm-9 col-md-10 main">

                    <div n:foreach="$flashes as $flash" class="alert alert-{$flash->type}" role="alert">{$flash->message}</div>

                    {* Javascriptové flashMessage *}
                    <div style="display: none;" class="alert alert-success"></div>
                    <div style="display: none;" class="alert alert-danger"></div>

                    {include content}

                </div>
            </div>
        </div><!--/.container-fluid-->

    </body>
</html>
