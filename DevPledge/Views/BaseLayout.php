<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Credit Rating Help</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/js/bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/js/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/js/bower_components/bootstrap/dist/css/bootstrap-theme.min.css">
    <link href="https://fonts.googleapis.com/css?family=Oleo+Script|Oswald|Palanquin:700" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/blog.css">

    <link href="/assets/css/main.css" rel="stylesheet" type="text/css"/>
    <script src="/assets/js/bower_components/jquery/dist/jquery.min.js"></script>
    <script src="/assets/js/bower_components/jquery-ui/jquery-ui.min.js"></script>
    <script src="/assets/js/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="/assets/js/giraffe/giraffe.1.1.js"></script>
    <script src="assets/js/main.js"></script>


</head>
<body>


<div class="blog-masthead">
    <div class="container">
        <div class="logo" title="Consumer Lady">

        </div>
    </div>
    <div class="container">
        <nav class="nav blog-nav">
            <a class="nav-link active" href="/">Home</a>
            <a class="nav-link" href="/">Trusted Products</a>
            <a class="nav-link" href="/">Financial Advice</a>
            <a class="nav-link" href="/">About</a>
        </nav>
    </div>
</div>
<div class="container">
    <div class="row">

        <div class="col-sm-8 blog-main ">
            <div class="blog-post">
				<?= $content ?>
            </div>
        </div>

        <div class="col-sm-3 offset-sm-1 blog-sidebar">
            <div class="sidebar-module sidebar-module-inset">
                <h4>About</h4>
                <p>Coral Davies, <em>Experienced Consumer Financial Consultant</em> - Helping like minded women over
                    come the consumer market.</p>
            </div>
            <div class="sidebar-module">
                <iframe style="width:120px;height:240px;" marginwidth="0" marginheight="0" scrolling="no"
                        frameborder="0"
                        src="//ws-eu.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=GB&source=ac&ref=tf_til&ad_type=product_link&tracking_id=consumerlad00-21&marketplace=amazon&region=GB&placement=B01JOASEJU&asins=B01JOASEJU&linkId=70d8155fdf542ffb2986264c312be33e&show_border=false&link_opens_in_new_window=true&price_color=333333&title_color=0066c0&bg_color=ffffff">
                </iframe>
                <h4>Archives</h4>
                <ol class="list-unstyled">

                    <li><a href="#">January 2018</a></li>
                    <li><a href="#">December 2017</a></li>
                    <li><a href="#">November 2017</a></li>
                    <li><a href="#">October 2017</a></li>
                    <li><a href="#">September 2017</a></li>
                    <li><a href="#">August 2017</a></li>
                    <li><a href="#">July 2017</a></li>
                    <li><a href="#">June 2017</a></li>
                    <li><a href="#">May 2017</a></li>
                    <li><a href="#">April 2017</a></li>
                </ol>
            </div>
            <div class="sidebar-module">
                <h4>Elsewhere</h4>
                <ol class="list-unstyled">
                    <li><a href="#">GitHub</a></li>
                    <li><a href="#">Twitter</a></li>
                    <li><a href="#">Facebook</a></li>
                </ol>
            </div>
        </div><!-- /.blog-sidebar -->
    </div>
</div>
<footer class="blog-footer">
    <p>ConsumerLady.co.uk &copy;2017</p>
    <p>
        <a href="#">Back to top</a>
    </p>
</footer>
</body>
</html>


