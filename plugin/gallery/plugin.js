

function cms_gallery_getKeystroke(ev) {
   evcb(ev); //Prevent keystrokes affecting underlying layers
  thiskey=((ev.which)||(ev.keyCode));
   // alert(thiskey);
  var thisimg=document.getElementById('cms_gallery_zimage');
  var thisdiv=document.getElementById('cms_gallery_zdiv');
  if (typeof oldposx=='undefined') oldposx=0;
  if (typeof oldposy=='undefined') oldposy=0;
  if (oldposx<0) oldposx=0;
  if (oldposx > thisimg.width) oldposx=thisimg.width;
  if (oldposy<0) oldposy=0;
  if (oldposy > thisimg.height) oldposy=thisimg.height;

  var delta=2; 
  
  switch(thiskey) {

  case 27: //z
    cms_gallery_unzoom();
  break;
  
  case 32: case 78: //space or n
    cms_gallery_goto('+1');
  break;

  case 16: case 66 : //shift or b
    cms_gallery_goto('-1');
  break;

  case 36: //home
    cms_gallery_goto('first');
  break;
  case 35: //end
    cms_gallery_goto('last');
  break;

  case 82: //r
    cms_gallery_goto('random');
  break;

  case 90: // z
    cms_gallery_togglezoom();
  break;

  case 85: // u
  cms_gallery_togglezoom(0);
  break;

  case 37: //left
    oldposx-=(delta*20);
    thisdiv.scrollLeft=oldposx;
   break;

  case 38: //up
    oldposy-=(delta*20);
    thisdiv.scrollTop=oldposy;
   break;

  case 39: //right
    oldposx+=(delta*20);
    thisdiv.scrollLeft=oldposx;
   break;
 
  case 40: //down
    oldposy+=(delta*20);
    thisdiv.scrollTop=oldposy;
   break;
  
  case 68: //d faster
    cms_gallery_setrate(-1);
   break;
  case 65: //a slower
    cms_gallery_setrate(1);
   break;
  case 83: //s slideshow
    cms_gallery_goto('slideshow');
   break;
  case 77: //m manual
   cms_gallery_goto('manual');
   break;
  case 67: //c 
   cms_gallery_showControls("toggle");
   break;
  }
}

function ActivateScroll(action) {
    // Allows translation of scroll wheel action for long images
  var x=document.getElementById('cms_gallery_zimage');
  if (action) {
    //adding the event listerner for Mozilla
    if(window.addEventListener)
        x.addEventListener('DOMMouseScroll', TranslateScroll, false);
    //for IE/OPERA etc
    x.onmousewheel = TranslateScroll;
  }else{
   if (x.removeEventListener) { // For all major browsers, except IE 8 and earlier
    x.removeEventListener('DOMMouseScroll', TranslateScroll, false);
    } else if (x.detachEvent) { // For IE 8 and earlier versions
    x.detachEvent('DOMMouseScroll', TranslateScroll, false);
    }
  }
}
  
function TranslateScroll(event){
    var thisimg=document.getElementById('cms_gallery_zimage');
    var thisdiv=document.getElementById('cms_gallery_zdiv');
    var delta = 0;
    if (!event) event = window.event;
    // normalize the delta
    if (event.wheelDelta) {
        // IE and Opera
        delta = event.wheelDelta / 60;
    } else if (event.detail) {
        // W3C
        delta = -event.detail / 2;
    }
    if (typeof oldposx=='undefined') oldposx=0;
    if (oldposx<0) oldposx=0;
    if (oldposx > thisimg.width) {
      oldposx=thisimg.width;
    } else {
      oldposx-=(delta*20);
    }
    thisdiv.scrollLeft=oldposx;
}


/**
 * @fileoverview dragscroll - scroll area by dragging
 * @version 0.0.5
 * 
 * @license MIT, see http://github.com/asvd/intence
 * @copyright 2015 asvd <heliosframework at gmail.com> 
 */


(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define(['exports'], factory);
    } else if (typeof exports !== 'undefined') {
        factory(exports);
    } else {
        factory((root.dragscroll = {}));
    }
}(this, function (exports) {
    var _window = window;
    var _document = document;
    var mousemove = 'mousemove';
    var mouseup = 'mouseup';
    var mousedown = 'mousedown';
    var EventListener = 'EventListener';
    var addEventListener = 'add'+EventListener;
    var removeEventListener = 'remove'+EventListener;

    var dragged = [];
    var reset = function(i, el) {
        for (i = 0; i < dragged.length;) {
            el = dragged[i++];
            el[removeEventListener](mousedown, el.md, 0);
            _window[removeEventListener](mouseup, el.mu, 0);
            _window[removeEventListener](mousemove, el.mm, 0);
        }

        dragged = _document.getElementsByClassName('dragscroll');
        for (i = 0; i < dragged.length;) {
            (function(el, lastClientX, lastClientY, pushed){
                el[addEventListener](
                    mousedown,
                    el.md = function(e) {
                        pushed = 1;
                        lastClientX = e.clientX;
                        lastClientY = e.clientY;
                        e.preventDefault();
                        e.stopPropagation();
                    }, 0
                );
                 
                 _window[addEventListener](
                     mouseup, el.mu = function() {pushed = 0;}, 0
                 );
                 
                _window[addEventListener](
                    mousemove,
                    el.mm = function(e, scroller) {
                        scroller = el.scroller||el;
                        if (pushed) {
                             scroller.scrollLeft -=
                                 (- lastClientX + (lastClientX=e.clientX));
                             scroller.scrollTop -=
                                 (- lastClientY + (lastClientY=e.clientY));
                        }
                    }, 0
                );
             })(dragged[i++]);
        }
    }

      
    if (_document.readyState == 'complete') {
        reset();
    } else {
        _window[addEventListener]('load', reset, 0);
    }

    exports.reset = reset;
}));

