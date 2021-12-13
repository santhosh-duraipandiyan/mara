<?php

/*
Disqus comments plugin. 
Takes Disqus account from the site's name in sitecfg.ini
Page (and therefore comment thread) identifier is taken from the page's file path relative to the website basedir.  
This is felt to be preferable to using the title, which can change due to editing. 
Be aware that for this reason, renaming or moving the disk file will lose your comments. 
This situation, if it arises, can be remedied by hardcoding a $disqus_identifier 
(equal to the previous path to the file) before the plugin call, in the affected page. 

To setup a Disqus account, go to disqus.com and create a login. 
Then, create a site account with the same name as your Mara cms site, as defined in sitecfg.ini.  
Insert a plugin call as <php include(plugin('disqus))?> at the place where you want the comments to be loaded 
(usually the foot of the main content area) on each page to have comments. 

*/ 

$disqus_shortname=strtolower(sitename);
if (!isset($disqus_identifier)) $disqus_identifier=strtolower(pagefile);
echo <<< END
<p><small>Site: $disqus_shortname Thread: $disqus_identifier</small></p>
<script>
    var disqus_shortname = '$disqus_shortname'; 
    var disqus_identifier = '$disqus_identifier';
</script>
<div id="disqus_thread"></div>
<script src='http://$disqus_shortname.disqus.com/embed.js' type='text/javascript'></script>
END;
?>

