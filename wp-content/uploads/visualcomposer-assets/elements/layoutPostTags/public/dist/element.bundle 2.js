(self.vcvWebpackJsonp4x=self.vcvWebpackJsonp4x||[]).push([["element"],{"./layoutPostTags/index.js":function(e,t,s){"use strict";var a=s("./node_modules/vc-cake/index.js"),o=s("./node_modules/@babel/runtime/helpers/esm/extends.js"),n=s("./node_modules/@babel/runtime/helpers/esm/classCallCheck.js"),l=s("./node_modules/@babel/runtime/helpers/esm/createClass.js"),r=s("./node_modules/@babel/runtime/helpers/esm/inherits.js"),u=s("./node_modules/@babel/runtime/helpers/esm/possibleConstructorReturn.js"),c=s("./node_modules/@babel/runtime/helpers/esm/getPrototypeOf.js"),i=s("./node_modules/react/index.js");function d(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}();return function(){var s,a=(0,c.default)(e);if(t){var o=(0,c.default)(this).constructor;s=Reflect.construct(a,arguments,o)}else s=a.apply(this,arguments);return(0,u.default)(this,s)}}var p=(0,a.getService)("api"),m=(0,a.getService)("dataManager"),g=function(e){(0,r.default)(s,e);var t=d(s);function s(){return(0,n.default)(this,s),t.apply(this,arguments)}return(0,l.default)(s,[{key:"getInnerHtml",value:function(e,t,s){var o=(0,a.getStorage)("settings").state("postData").get(),n=o.layout_post_tags_list_link;s&&n.forEach((function(e,t){var a='<a style="'+s+'" href';n[t]=e.replace("<a href",a)}));var l=t.replace(/<!--(.*?)-->/gm,"");if(this.checkIsLayout(o.post_type_slug)){var r=[],u=m.get("localizations"),c=u.layoutPostTagsFirstTag?u.layoutPostTagsFirstTag:"First category",i=u.layoutPostTagsSecondTag?u.layoutPostTagsSecondTag:"Second category",d=u.layoutPostTagsThirdTag?u.layoutPostTagsThirdTag:"Third category";if(s){var p='<a style="'+s+'" href';r=["<a "+p+' href="#">'+c+"</a>","<a "+p+' href="#">'+i+"</a>","<a "+p+' href="#">'+d+"</a>"]}else r=['<a href="#">'+c+"</a>",'<a href="#">'+i+"</a>",'<a href="#">'+d+"</a>"];return l.replace(">layout_placeholder<",">"+r.join(e)+"<")}if(!n.length)return l.replace(">layout_placeholder<",">"+window.vcvLayoutPostTagsEmptyMessage+"<");var g=n.join(e);return l.replace(">layout_placeholder<",">"+g+"<")}},{key:"checkIsLayout",value:function(e){return["vcv_templates","vcv_headers","vcv_footers","vcv_sidebars","vcv_layouts"].indexOf(e)>-1}},{key:"getTinyMceStyles",value:function(e){var t="",s=e.match(/<span style="(.*)">(.*)<!-- wp:vcv-gutenberg-blocks\/dynamic-field-block {"value":"layout_post_tags_list_placeholder"} -->/m);return s&&s[1]&&(t=s[1]),t}},{key:"render",value:function(){var e=this.props,t=e.id,s=e.atts,a=e.editor,n=s.output,l=s.customClass,r=s.metaCustomId,u=["vce-layouts-post-tags-container"],c={};l&&u.push(l),r&&(c.id=r);var d=this.applyDO("all"),p=n.props["data-vcvs-html"],m=this.getTinyMceStyles(this.props.rawAtts.output);return i.createElement("div",(0,o.default)({className:u.join(" ")},a,c),i.createElement("div",(0,o.default)({className:"vce-layouts-post-tags vce vcvhelper",id:"el-"+t},d,{"data-vcvs-html":p,dangerouslySetInnerHTML:{__html:this.getInnerHtml(s.separator,p,m)}})))}}]),s}(p.elementComponent),y=(0,a.getStorage)("hubElements"),v=function(){(0,(0,a.getService)("cook").add)(s("./layoutPostTags/settings.json"),(function(e){e.add(g)}),{css:s("./node_modules/raw-loader/index.js!./layoutPostTags/styles.css"),mixins:{}})},f=function(){var e=y.state("elementTeasers").get(),t=e[0].elements.findIndex((function(e){return"Post Tags"===e.name}));t>-1&&e[0].elements[t]&&(e[0].elements[t].disabledOnHub=!0,y.state("elementTeasers").set(e))},h=void 0!==window.VCV_UPDATE_ACTIONS,T=window.VCV_EDITOR_TYPE?window.VCV_EDITOR_TYPE():"default";"vcv_layouts"===T||h?(v(),(0,a.getStorage)("settings").state("layoutType").onChange((function(e){var t,s;"postTemplate"===e?(v(),t=y.state("elementTeasers").get(),(s=t[0].elements.findIndex((function(e){return"Post Tags"===e.name})))>-1&&t[0].elements[s]&&(delete t[0].elements[s].disabledOnHub,y.state("elementTeasers").set(t))):((0,a.getStorage)("elementSettings").trigger("remove","layoutPostTags"),f())}))):"template"===T||"default"===T?v():y.state("elementTeasers").onChange((function e(){window.setTimeout((function(){f()}),500),y.state("elementTeasers").ignoreChange(e)}))},"./node_modules/raw-loader/index.js!./layoutPostTags/styles.css":function(e){e.exports="div.vce-layouts-post-tags-container p a {\n    border-bottom-color: currentColor;\n}"},"./layoutPostTags/settings.json":function(e){"use strict";e.exports=JSON.parse('{"tag":{"type":"string","access":"protected","value":"layoutPostTags"},"output":{"type":"htmleditor","access":"public","value":"<p>\x3c!-- wp:vcv-gutenberg-blocks/dynamic-field-block {\\"value\\":\\"layout_post_tags_list_placeholder\\"} --\x3e\x3c!-- /wp:vcv-gutenberg-blocks/dynamic-field-block --\x3e</p>","options":{"onlyDynamic":true,"dynamicField":true,"dynamicFieldsOptions":{"addAttributes":["separator"]}}},"separator":{"type":"string","access":"public","value":", ","options":{"label":"Separator"}},"designOptions":{"type":"designOptions","access":"public","value":{},"options":{"label":"Design Options"}},"editFormTab1":{"type":"group","access":"protected","value":["output","separator","metaCustomId","customClass"],"options":{"label":"General"}},"metaEditFormTabs":{"type":"group","access":"protected","value":["editFormTab1","designOptions"]},"relatedTo":{"type":"group","access":"protected","value":["General"]},"customClass":{"type":"string","access":"public","value":"","options":{"label":"Extra class name","description":"Add an extra class name to the element and refer to it from the custom CSS option."}},"metaCustomId":{"type":"customId","access":"public","value":"","options":{"label":"Element ID","description":"Apply a unique ID to the element to link it directly by using #your_id (for element ID use lowercase input only)."}}}')}},[["./layoutPostTags/index.js"]]]);