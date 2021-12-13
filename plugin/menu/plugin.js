
function menu_eventhandler(thisdd, thisaction){
  // stays on screen for testing...
  // return;
  // sets js global to indicate open dropdown menu object. 
  if (typeof cms_menutimer!=='undefined'){ clearTimeout(cms_menutimer)}
  if (thisaction=="init") {
   // Removed to fix no-repeat-click bug (5.3) 
   // menu_ddclose();
  }  
  if (thisaction=="init" || thisaction=="out") {
  var thistype=typeof thisdd;
  if (thistype.indexOf('object')>=0) {
    cms_active_dropdown=thisdd;
  }
    if (topmenu_timeout>0){
      cms_menutimer=setTimeout("menu_ddclose()",topmenu_timeout*1000);
    }
  }
}

function menu_burgerclick(thisburger){
  var menucontainer=thisburger.nextSibling;
  if (typeof menucontainer.tagName=='undefined'){menucontainer=menucontainer.nextSibling}
   var mcn= menucontainer.className ;
   var mcns=' ' + mcn + ' ';
   if (mcns.indexOf(' menu_show ') == -1){
     mcn = mcn + ' menu_show'; 
     menucontainer.className=mcn;
     thisburger.className='menu_burger_clicked';
   } else {
     mcn=mcn.replace('menu_show','').trim(); 
     menucontainer.className=mcn;
     thisburger.className='menu_burger';
   } 
  thisburger.cancelBubble=true;
  return false;
}

function menu_ddclose(){
 var thistype=typeof cms_active_dropdown;
 if (thistype.indexOf('object')>=0) {
   cms_active_dropdown.style.display='none';
   cms_active_dropdown="";
 }
}

function menu_dropdown(clickeditem){
  if (typeof cms_menutimer!=='undefined'){ clearTimeout(cms_menutimer)}
  // Presently all dd menus are referenced by last click
 if (clickeditem==''){var clickeditem=document.activeElement}
    var mlcontainer=clickeditem.parentNode;
    var mlnodes=mlcontainer.childNodes;

  
// Get dropdown menu headers and sections into matched arrays: 
   var ddheaders=[];
   var ddsections=[];
   for (ct=0; ct<mlnodes.length; ct++) {
     if(mlnodes[ct].nodeType==1){
       var thisnodename=mlnodes[ct].nodeName.toLowerCase();
       if (thisnodename=='a'){
         if (mlnodes[ct].className.indexOf('menu_dropdown') >= 0) {
           ddheaders[ddheaders.length]=mlnodes[ct];
         }
       }
       if (thisnodename=='div'){
         if (mlnodes[ct].className.indexOf('menu_dropdown') >= 0){
           ddsections[ddsections.length]=mlnodes[ct];
         }
       }
     }
   }

// Identify current dropdown selection and show; optionally hide others. 
  if (ddsections.length==ddheaders.length){
    for (ct=0; ct<ddsections.length; ct++) {
      if (ddheaders[ct]==clickeditem && ddsections[ct].style.display!='block') {
        ddsections[ct].style.display='block';
        // Calculate h position for dropdown item, preventing offpage-to-right placement.
        // Note that early browsers do not support this method, in which case a static 100px location is used.  
        try {
          var newpos=ddheaders[ct].offsetLeft;
        } catch (e){
          var newpos=100;
        }
        try {
          var ddsrect = ddsections[ct].getBoundingClientRect();
          var ddwidth=ddsrect.right-ddsrect.left;
        } catch (e) {
          var ddwidth=100;
        }
        // test for out-of-page menu rhs: 
        var vpWidth = 4096;
        if( typeof( window.innerWidth ) == 'number' ) {
        //Non-IE
        vpWidth = window.innerWidth;
        } else if( document.documentElement && document.documentElement.clientWidth ) {
        //IE 6+ in 'standards compliant mode'
        vpWidth = document.documentElement.clientWidth;
        } else if( document.body && document.body.clientWidth ) {
        //IE 4 compatible
        vpWidth = document.body.clientWidth;
        }
         var ddoverlap=newpos+ddwidth-vpWidth
        if (ddoverlap>0){
          newpos=newpos-ddoverlap-10; 
        }
        ddsections[ct].style.position='absolute';
        ddsections[ct].style.left=newpos +'px';
        menu_eventhandler(ddsections[ct],'init')
        ddsections[ct].onmouseover=function() {menu_eventhandler(ddsections[ct],'in')};
        ddsections[ct].onmouseout=function() {menu_eventhandler(ddsections[ct],'out')};
      } else { 
        ddsections[ct].onmouseover=null;
        ddsections[ct].onmouseout=null;
        ddsections[ct].style.display='none';
      }
    }
  }else{
    alert('Mismatched number of menu headers and sections.')
  }
  return false;
}


 function show_menu(clickeditem) {
  // Show clicked menu section - optionally close others. 
  if (clickeditem==''){var clickeditem=document.activeElement}
  var mlcontainer=clickeditem.parentNode;
  var mlnodes=mlcontainer.childNodes;
  var in_active=false;
   for (ct=0; ct<mlnodes.length; ct++) {
     if(mlnodes[ct].nodeType==1){
       var thisnodename=mlnodes[ct].nodeName.toLowerCase();
       if (thisnodename=='a'){
         if (mlnodes[ct]==clickeditem){
           in_active=true;
         }else{
           if ( sidemenu_autoclose==true && mlnodes[ct].className=='menu_open') {mlnodes[ct].className='menu_closed';}
         }
       }

       if (thisnodename=='div'){
         if (in_active){
           in_active=false;
           if (mlnodes[ct].className=='menu_closed') { 
               clickeditem.className='menu_open';
               clickeditem.style.display='block';
               mlnodes[ct].className='menu_open';
             }else{
                 mlnodes[ct].className='menu_closed';
                 clickeditem.className='menu_closed';
             }
         }else{
            if ( sidemenu_autoclose==true && mlnodes[ct].className=='menu_open') mlnodes[ct].className='menu_closed';
         }
       }
     }
   }

  return false;
 }


function menu_show_active_tree(selected_id) {
// Operates on page load to reveal selected page's link in menu.
// return; //temp disabled
// Shows part of menu tree containing open page link
// Walk up through DOM tree, turning on display of all menu divs with closed class until non-menu div encountered.. 
var recursions=6
  var si=document.getElementById(selected_id);
//  selected_item.style.background='green';
 for (ct=1;ct<=recursions;ct++){
  var thisnode=si.parentNode;
  if(thisnode.nodeType==1){
    var tnn=thisnode.nodeName;
    tnn=tnn.toLowerCase();
    if(tnn=='div'){
      var tncn=thisnode.className;
      tncn=tncn.toLowerCase();
      if (tncn.indexOf('menu_closed')>=0){
        thisnode.className='menu_open';
        // locate section header and change icon;
        ps=thisnode;
        for (pnct=1;pnct<100;pnct++){
          var ps=ps.previousSibling;
          if (ps==null){break;}
          if (ps.nodeType==1){
            var pnn=ps.nodeName;
            pnn=pnn.toLowerCase();
            if (pnn=='a'){
             ps.className='menu_open';
             break;
            }
          }
        }
      } else {
        return;
      }
    }else if (tnn != 'a') {
      return;
    }
    si=thisnode;
  }
 }
}

/*
     if(mlnodes[ct].nodeType==1){
       var thisnodename=mlnodes[ct].nodeName.toLowerCase();
       if (thisnodename=='a'){
         if (mlnodes[ct].className.indexOf('menu_dropdown') >= 0) {
           ddheaders[ddheaders.length]=mlnodes[ct];
         }
       }
       if (thisnodename=='div'){
         if (mlnodes[ct].className.indexOf('menu_dropdown') >= 0){
           ddsections[ddsections.length]=mlnodes[ct];

*/

