"use strict";(self.vcvWebpackJsonp4x=self.vcvWebpackJsonp4x||[]).push([["element"],{"./addon/extendedEditForm/index.js":function(e,a,t){var l=t("./node_modules/@babel/runtime/helpers/esm/toConsumableArray.js"),n=t("./node_modules/vc-cake/index.js"),i=JSON.parse('[{"fieldKey":"parallax","value":"tilt","label":"Tilt","library":"vanillaTilt","dependencies":[]},{"fieldKey":"parallax","value":"tilt-glare","label":"Tilt glare","library":"vanillaTilt","dependencies":[]},{"fieldKey":"parallax","value":"tilt-reverse","label":"Tilt reverse","library":"vanillaTilt","dependencies":[]},{"fieldKey":"parallax","value":"tilt-reset","label":"Tilt reset","library":"vanillaTilt","dependencies":[]},{"fieldKey":"parallax","value":"backgroundAnimation","label":"Mouse follow animation","description":"Animation will work with multiple images selected as a background.","library":"backgroundAnimation","dependencies":["anime"]}]'),r=t("./node_modules/@babel/runtime/helpers/esm/classCallCheck.js"),s=t("./node_modules/@babel/runtime/helpers/esm/createClass.js"),o=(0,n.getService)("document"),d=(0,n.getService)("cook"),c=(0,n.getStorage)("assets"),u=function(){function e(){(0,r.default)(this,e)}return(0,s.default)(e,[{key:"removeAttrs",value:function(e,a){e.forEach((function(e){var t=e.split(/(?=[A-Z])/).join("-").toLowerCase();a.removeAttribute("data-".concat(t))})),a.style.willChange="",a.style.transition="",a.style.transform="",a.classList.remove("vce-tilt")}},{key:"handleParallaxOptions",value:function(e){var a=e.id,t=e.selector,l=d.getById(a).getAll();if(!l.parallax||(!Array.isArray(l.parallax)||l.parallax.length)&&l.parallax.device){var n=t||"#el-".concat(a),r=document.getElementById("vcv-editor-iframe");if(r){var s=r.contentDocument.querySelector(n);if(s){var u=Object.keys(s.dataset).filter((function(e){return e.toLocaleLowerCase().indexOf("tilt")>-1})),v=l.parallax.device,f=Object.keys(v);u.length&&this.removeAttrs(u,s);var m=!1;f.forEach((function(e){var t=v[e].parallax,l=i.find((function(e){return e.value===t}));if(!m&&l){m=l;var n=o.get(a);c.trigger("editSharedLibrary",n)}if(l&&l.value.indexOf("tilt")>-1){var r=v[e].parallaxEnable?"".concat(v[e].parallaxEnable,":").concat(t):v[e].parallaxEnable;s.setAttribute("data-vce-tilt-".concat(e),r),s.classList.add("vce-tilt")}}))}else console.warn("Parallax DOM element not found!")}}}}]),e}(),v=t("./node_modules/@babel/runtime/helpers/esm/extends.js"),f=t("./node_modules/react/index.js"),m=t("./node_modules/classnames/index.js"),p=t.n(m),b=t("./node_modules/react-dom/server.browser.js");function g(e){var a=e.deviceKey,t=e.content,l={"data-vce-asset-background-animation":!0,"data-vce-asset-background-animation-element":e.atts.id},n=["vce-asset-background-animation-container","vce-visible-".concat(a,"-only")],i=["vce-asset-background-animation"];i.push("vcvhelper");var r=(0,b.renderToStaticMarkup)(f.createElement("div",{className:"vce-asset-background-animation"},t));return f.createElement("div",(0,v.default)({className:p()(n)},l),f.createElement("div",{className:p()(i),"data-vcvs-html":r}))}var y,x=(0,n.getService)("cook"),h=(0,n.getStorage)("fieldOptions"),k=(0,n.getStorage)("elementsSettings"),A=(0,n.getStorage)("assets"),T=["parallax"],O=new u;function j(e){T.includes(e.fieldType)&&"parallax"===e.fieldType&&O.handleParallaxOptions(e)}function E(e){var a=k.state("extendedOptions").get(),t=a&&a.elements?a.elements:[],l=t.findIndex((function(a){return a.id===e.id}));l<0?t.push(e):(t[l].id=e.id,t[l].fieldKey=e.fieldKey,t[l].fieldType=e.fieldType),k.state("extendedOptions").set({elements:t,backgroundAnimationComponent:g})}y=(y=A.state("attributeLibs").get())?[y].concat((0,l.default)(i)):i,A.state("attributeLibs").set(y),h.on("fieldOptions",(function(e,a){var t;a.values=a.values.filter((function(e){return!e.disabled}));var n=i.filter((function(a){return e===a.fieldKey}));(t=a.values).push.apply(t,(0,l.default)(n)),h.state("currentAttribute:settings").set(a)})),h.on("fieldOptionsChange",(function(e){T.includes(e.fieldType)?E(e):function(e){var a=x.getById(e.id).getAll(),t=e.id;T.forEach((function(e){"parallax"===e&&a[e]&&E({fieldKey:e,fieldType:e,id:t})}))}(e)})),k.state("elementOptions").onChange(j)}},[["./addon/extendedEditForm/index.js"]]]);