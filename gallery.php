<?php include_once 'codebase/reflex.php' ?>
<!doctype html>
<html>
<head>
   <TITLE></TITLE>
   <META NANE="description"  CONTENT="">
   <META NAME="keywords" CONTENT="">
   <META NAME="cms_theme" CONTENT="">
   <META NAME="cms_menu" CONTENT="">
   <META NAME="cms_hide" CONTENT="">
   <META HTTP-EQUIV="Content-Type" CONTENT="text/html; utf-8">
</head>

<body id='cms_gallery_b1' >
<h1>Your Gallery</h1>

<p>This page uses the Mara Gallery plugin, and automatically loads images found in the img/gallery folder. We put a few test images in, courtesy of Wikimedia Commons.</p>

<p>To use, just upload or copy some images and modify this text to suit your requirements. Indexing of the images happens on page reload, is completely automatic. For very large collections, due to server time limits it may take several page reloads to index all of the images. Once indexed, they are available to all site visitors as a contact sheet.</p>

<p>It is suggested that you use images of sizes from 800x600 up to 1900x1600, as this will suit fullscreen viewing on most desktop equipment. The contact sheet is extremely tolerant of varying aspect ratios, thus portraits and panoramas can be included in the same gallery.</p>

<p>It really is as simple as that- Put the images up, refresh, and it works. Absolutely no forms to fill or boxes to tick.</p>

<p>Images are listed in &#39;natural sorting&#39; order; that is, 1,2,3,10,11,12 etc where numeric filenames are used, case insensitive alphabetic order where names are used.</p>

<p>When in page editing mode, the gallery script will appear as red placeholder just below here. To see the code needed to add a gallery to a page, double-click this placeholder.</p>

<p>To see the gallery working when in editing mode, use the Preview button on the editor toolbar, at bottom left next to Save.</p>

<hr />
<?php 
include(plugin('gallery'));
gallery_browser("img/gallery",10,"120px");
?>

<hr />
<h3>Controls:</h3>

<p>To zoom an image from the contact sheet to full-view mode, click it once.</p>

<p>To see the zoomed image controls, click the zoomed image once near the center.&nbsp; A toolbar at the foot of the window contains the most-used controls.&nbsp; A design principle is that the <em>control zones are on the browser window rather than on the image.</em> Thus, they stay the same size and in the same place even if the image size changes.</p>

<p>Image change is simply a matter of clicking at the left or right side of the browser window. This works regardless of whether the controls are visible or hidden. There is a close button at top right, and zoom/unzoom button to the left of it, which become visible when the mouse is hovered over them.</p>

<p>Touchscreen users may tap the relevant zones to advance or replay the slides, even when the zones are not visible.</p>

<h4>Keyboard Controls in zoomed mode:</h4>

<p>N or Space - Next image.<br />
B - Previous image. (back)<br />
Z - Cycle through zoom modes.<br />
U - Unzoom (default fullsize mode)<br />
S - Start/stop slideshow.<br />
M - Stop slideshow. (manual)<br />
A - Slower.<br />
D - Faster.<br />
R - Random image, or toggle random order on/off in slideshow mode.<br />
C - Show controls.<br />
Esc - Return to contact sheet.</p>

<h4>Zoom modes:</h4>

<p class="brbr">0 - Default - Show actual size if smaller than browser window, constrain to window dimensions if larger.<br />
1&nbsp; - Enlarge images up to a max of 2x or window dimensions, whichever is smaller.<br />
2 - Enlarge images to cover whole browser window, with unidirectional scrolling where required. 2x max enlargement.<br />
3 - Enlarge to cover window, but show images larger than window at native size, with bidirectional scrolling where required.&nbsp;</p>

<p><sub>Note that some zoom modes will have no effect if the detail is too low to allow satisfactory display at that size.</sub></p>

<hr />
</body>
</html>