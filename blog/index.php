<?php include_once '../codebase/reflex.php' ?>
<!doctype html>
<html>
<head><title>Blog Index Page</title>
   <META NAME="creation_time" CONTENT="1440000200">
   <META NAME="description" CONTENT="">
   <META NAME="keywords" CONTENT="">
   <META NAME="cms_theme" CONTENT=".blog">
   <META HTTP-EQUIV="Content-Type" CONTENT="text/html; utf-8">
</head>
<body>
<h2>Blog table of contents</h2>

<p>The article list below is automatically generated. You can however add any preface text you like in here, and it will appear at the top of the TOC. Pages added to the blog will be included here at the next refresh, which is typically after a few minutes. To see any changes in the TOC immediately, call this page with a ?rebuild=1 parameter.</p>

<p>Blog pages can be created and edited in the normal way. They should be based on the Blog template, and saved as blog/filename</p>

<p>Where the blog theme supports TOC images, the first image in any blog page which has alt text will be used.</p>

<p>The Title and shortform of each TOC entry will be taken from the first heading and first paragraph of the page, so it pays to think a little about these items so as to create a meaningful TOC listing.</p>

<h2>Recent articles</h2>

<?php if (strlen(get('theme'))>0 && stripos(get('theme'),'blog')===false) echo "Note: This blog index page will display as blank in a theme intended for static content"; ?>

</body>
</html>