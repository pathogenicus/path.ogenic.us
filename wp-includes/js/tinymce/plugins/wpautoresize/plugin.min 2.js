tinymce.PluginManager.add("wpautoresize",function(g){var m=g.settings,h=300,c=!1;function f(e){return parseInt(e,10)||0}function y(e){var t,o,n,i,a,s,l,u,r,d=tinymce.DOM;c&&(o=g.getDoc())&&(e=e||{},t=o.body,o=o.documentElement,n=m.autoresize_min_height,!t||e&&"setcontent"===e.type&&e.initial||g.plugins.fullscreen&&g.plugins.fullscreen.isFullscreen()?t&&o&&(t.style.overflowY="auto",o.style.overflowY="auto"):(i=g.dom.getStyle(t,"margin-top",!0),a=g.dom.getStyle(t,"margin-bottom",!0),s=g.dom.getStyle(t,"padding-top",!0),l=g.dom.getStyle(t,"padding-bottom",!0),u=g.dom.getStyle(t,"border-top-width",!0),r=g.dom.getStyle(t,"border-bottom-width",!0),(i=t.offsetHeight+f(i)+f(a)+f(s)+f(l)+f(u)+f(r))&&i<o.offsetHeight&&(i=o.offsetHeight),(i=isNaN(i)||i<=0?tinymce.Env.ie?t.scrollHeight:tinymce.Env.webkit&&0===t.clientHeight?0:t.offsetHeight:i)>m.autoresize_min_height&&(n=i),m.autoresize_max_height&&i>m.autoresize_max_height?(n=m.autoresize_max_height,t.style.overflowY="auto",o.style.overflowY="auto"):(t.style.overflowY="hidden",o.style.overflowY="hidden",t.scrollTop=0),n!==h&&(a=n-h,d.setStyle(g.iframeElement,"height",n+"px"),h=n,tinymce.isWebKit&&a<0&&y(e),g.fire("wp-autoresize",{height:n,deltaHeight:"nodechange"===e.type?a:null}))))}function n(e,t,o){setTimeout(function(){y(),e--?n(e,t,o):o&&o()},t)}g.settings.inline||tinymce.Env.iOS||(m.autoresize_min_height=parseInt(g.getParam("autoresize_min_height",g.getElement().offsetHeight),10),m.autoresize_max_height=parseInt(g.getParam("autoresize_max_height",0),10),m.wp_autoresize_on&&(c=!0,g.on("init",function(){g.dom.addClass(g.getBody(),"wp-autoresize")}),g.on("nodechange keyup FullscreenStateChanged",y),g.on("setcontent",function(){n(3,100)}),g.getParam("autoresize_on_init",!0)&&g.on("init",function(){n(10,200,function(){n(5,1e3)})})),g.on("show",function(){h=0}),g.addCommand("wpAutoResize",y),g.addCommand("wpAutoResizeOn",function(){g.dom.hasClass(g.getBody(),"wp-autoresize")||(c=!0,g.dom.addClass(g.getBody(),"wp-autoresize"),g.on("nodechange setcontent keyup FullscreenStateChanged",y),y())}),g.addCommand("wpAutoResizeOff",function(){var e;m.wp_autoresize_on||(c=!1,e=g.getDoc(),g.dom.removeClass(g.getBody(),"wp-autoresize"),g.off("nodechange setcontent keyup FullscreenStateChanged",y),e.body.style.overflowY="auto",e.documentElement.style.overflowY="auto",h=0)}))});