<?php
require('/home/mackeral/Web/phpIncludes/config.php');
$page = new StatsPage('Home Page');
$page->addInternalCSS('.repoContainer {
	padding: 40px 15px;
	text-align: center;
}
#carousel { width: 500px; margin: 0 auto; }
.navbar-inverse .navbar-brand { color: white; }');
$page->addContent(HTMLLib::p("Repository statistics don't have to be tricky.<br>But when they are, I like them with a flaky crust.", array('class'=>'lead')));
$page->addContent(HTMLLib::div(
    HTMLLib::comment('Indicators') . 
    HTMLLib::ol(
        array(
            HTMLLib::li('', array('data-target'=>'#carousel-example-generic', 'data-slide-to'=>'0', 'class'=>'active')),
            HTMLLib::li('', array('data-target'=>'#carousel-example-generic', 'data-slide-to'=>'1')),
            HTMLLib::li('', array('data-target'=>'#carousel-example-generic', 'data-slide-to'=>'2'))
        ),
        array('class'=>'carousel-indicators'),
        false
    ) . 
    HTMLLib::comment('Wrapper for slides') . 
    HTMLLib::div(
        HTMLLib::div(
            HTMLLib::img('http://placehold.it/500x375', '...') . 
            HTMLLib::div(HTMLLib::a('Browse and Search', '/stats/index2.php'), array('class'=>'carousel-caption'))
        , array('class'=>'item active')) .
        HTMLLib::div(
            HTMLLib::img('http://placehold.it/500x375', '...') . 
            HTMLLib::div('caption', array('class'=>'carousel-caption'))
        , array('class'=>'item')) .
        HTMLLib::div(
            HTMLLib::img('http://placehold.it/500x375', '...') . 
            HTMLLib::div('caption', array('class'=>'carousel-caption'))
        , array('class'=>'item'))
    , array('class'=>'carousel-inner')) . 
    HTMLLib::comment('Controls') . 
    HTMLLib::a(HTMLLib::span('', array('class'=>'icon-pref')), '#carousel-example-generic', array('class'=>'left carousel-control', 'data-slide'=>'prev')) . 
    HTMLLib::a(HTMLLib::span('', array('class'=>'icon-next')), '#carousel-example-generic', array('class'=>'right carousel-control', 'data-slide'=>'next'))
, array('id'=>'carousel','class'=>'carousel slide')));
$page->addScript('$(".carousel").carousel({
  interval: 5000
})');
echo $page;
?>