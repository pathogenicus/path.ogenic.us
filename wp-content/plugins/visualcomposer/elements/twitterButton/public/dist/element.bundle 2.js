/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
(self["vcvWebpackJsonp4x"] = self["vcvWebpackJsonp4x"] || []).push([["element"],{

/***/ "./twitterButton/component.js":
/*!************************************!*\
  !*** ./twitterButton/component.js ***!
  \************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": function() { return /* binding */ TwitterButton; }\n/* harmony export */ });\n/* harmony import */ var _babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/extends */ \"./node_modules/@babel/runtime/helpers/esm/extends.js\");\n/* harmony import */ var _babel_runtime_helpers_typeof__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/typeof */ \"./node_modules/@babel/runtime/helpers/esm/typeof.js\");\n/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ \"./node_modules/@babel/runtime/helpers/esm/classCallCheck.js\");\n/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ \"./node_modules/@babel/runtime/helpers/esm/createClass.js\");\n/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ \"./node_modules/@babel/runtime/helpers/esm/inherits.js\");\n/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ \"./node_modules/@babel/runtime/helpers/esm/possibleConstructorReturn.js\");\n/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ \"./node_modules/@babel/runtime/helpers/esm/getPrototypeOf.js\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! react */ \"./node_modules/react/index.js\");\n/* harmony import */ var vc_cake__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! vc-cake */ \"./node_modules/vc-cake/index.js\");\n/* harmony import */ var vc_cake__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(vc_cake__WEBPACK_IMPORTED_MODULE_8__);\n/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! lodash */ \"./node_modules/lodash/lodash.js\");\n/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_9___default = /*#__PURE__*/__webpack_require__.n(lodash__WEBPACK_IMPORTED_MODULE_9__);\n\n\n\n\n\n\n\n\nfunction _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = (0,_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_6__[\"default\"])(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = (0,_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_6__[\"default\"])(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return (0,_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_5__[\"default\"])(this, result); }; }\n\nfunction _isNativeReflectConstruct() { if (typeof Reflect === \"undefined\" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === \"function\") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }\n\n\n\n\nvar vcvAPI = vc_cake__WEBPACK_IMPORTED_MODULE_8___default().getService('api');\n\nvar TwitterButton = /*#__PURE__*/function (_vcvAPI$elementCompon) {\n  (0,_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4__[\"default\"])(TwitterButton, _vcvAPI$elementCompon);\n\n  var _super = _createSuper(TwitterButton);\n\n  function TwitterButton() {\n    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(this, TwitterButton);\n\n    return _super.apply(this, arguments);\n  }\n\n  (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_3__[\"default\"])(TwitterButton, [{\n    key: \"componentDidMount\",\n    value: function componentDidMount() {\n      this.insertTwitterJs(this.props.atts);\n    }\n  }, {\n    key: \"componentDidUpdate\",\n    value: function componentDidUpdate(prevProps) {\n      if (!(0,lodash__WEBPACK_IMPORTED_MODULE_9__.isEqual)(this.props.atts, prevProps.atts)) {\n        var _prevProps$atts = prevProps.atts,\n            shareText = _prevProps$atts.shareText,\n            tweetAccount = _prevProps$atts.tweetAccount,\n            tweetButtonSize = _prevProps$atts.tweetButtonSize,\n            buttonType = _prevProps$atts.buttonType,\n            username = _prevProps$atts.username,\n            showUsername = _prevProps$atts.showUsername,\n            hashtagTopic = _prevProps$atts.hashtagTopic,\n            tweetText = _prevProps$atts.tweetText;\n        var elementKey = \"customProps:\".concat(prevProps.id, \"-\").concat(buttonType, \"-\").concat(shareText, \"-\").concat(tweetAccount, \"-\").concat(tweetButtonSize, \"-\").concat(username, \"-\").concat(showUsername, \"-\").concat(hashtagTopic, \"-\").concat(tweetText);\n        var nextAtts = this.props.atts;\n        var nextElementKey = \"customProps:\".concat(this.props.id, \"-\").concat(nextAtts.buttonType, \"-\").concat(nextAtts.shareText, \"-\").concat(nextAtts.tweetAccount, \"-\").concat(nextAtts.tweetButtonSize, \"-\").concat(nextAtts.username, \"-\").concat(nextAtts.showUsername, \"-\").concat(nextAtts.hashtagTopic, \"-\").concat(nextAtts.tweetText);\n\n        if (elementKey !== nextElementKey) {\n          this.insertTwitterJs(this.props.atts);\n        }\n      }\n    }\n  }, {\n    key: \"insertTwitterJs\",\n    value: function insertTwitterJs(props) {\n      var tag = this.createElementTag(props);\n      var twitterScript = '<script async src=\"https://platform.twitter.com/widgets.js\" charset=\"utf-8\"></script>';\n      twitterScript = tag + twitterScript;\n      var wrapper = this.getDomNode().querySelector('.vce-tweet-button-inner');\n      this.updateInlineHtml(wrapper, twitterScript);\n    }\n  }, {\n    key: \"extractDynamicContent\",\n    value: function extractDynamicContent(content) {\n      if ((0,_babel_runtime_helpers_typeof__WEBPACK_IMPORTED_MODULE_1__[\"default\"])(content) !== 'object') {\n        return content;\n      }\n\n      var contentProps = content.props;\n\n      if (contentProps) {\n        return contentProps.dangerouslySetInnerHTML.__html;\n      }\n    }\n  }, {\n    key: \"createElementTag\",\n    value: function createElementTag(props) {\n      var element = document.createElement('a');\n      var shareText = props.shareText,\n          tweetAccount = props.tweetAccount,\n          tweetButtonSize = props.tweetButtonSize,\n          buttonType = props.buttonType,\n          username = props.username,\n          showUsername = props.showUsername,\n          hashtagTopic = props.hashtagTopic,\n          tweetText = props.tweetText;\n      var buttonClass = 'twitter-' + buttonType + '-button';\n\n      if (buttonType && buttonType === 'share' && shareText) {\n        element.setAttribute('data-text', this.extractDynamicContent(shareText));\n      }\n\n      if (buttonType && (buttonType === 'mention' || buttonType === 'hashtag') && tweetText) {\n        element.setAttribute('data-text', this.extractDynamicContent(tweetText));\n      }\n\n      if (buttonType && buttonType === 'share' && tweetAccount) {\n        tweetAccount = tweetAccount.split('@').pop();\n        element.setAttribute('data-via', tweetAccount);\n      }\n\n      if (tweetButtonSize && tweetButtonSize === 'large') {\n        element.setAttribute('data-size', tweetButtonSize);\n      }\n\n      if (username) {\n        username = username.split('@').pop();\n        username = username.split('https://twitter.com/').pop();\n        username = username.replace(/\\s+/g, '');\n      }\n\n      if (hashtagTopic) {\n        hashtagTopic = hashtagTopic.split('https://twitter.com/hashtag/').pop();\n        hashtagTopic = hashtagTopic.replace('?src=hash', '');\n        hashtagTopic = hashtagTopic.replace(/\\s+/g, '');\n      }\n\n      if (buttonType && buttonType === 'follow') {\n        element.setAttribute('data-show-screen-name', showUsername.toString());\n      }\n\n      var links = {\n        share: 'https://twitter.com/share',\n        follow: 'https://twitter.com/' + username,\n        mention: 'https://twitter.com/intent/tweet?screen_name=' + username,\n        hashtag: 'https://twitter.com/intent/tweet?button_hashtag=' + hashtagTopic\n      };\n      var buttonLink = links[buttonType];\n      var defaultContent = {\n        share: 'Tweet',\n        follow: showUsername ? 'Follow @' + username : 'Follow',\n        mention: 'Tweet to @' + username,\n        hashtag: 'Tweet #' + hashtagTopic ? hashtagTopic.split('#').pop() : 0\n      };\n      var buttonContent = defaultContent[buttonType];\n      element.setAttribute('href', buttonLink);\n      element.setAttribute('data-show-count', 'false');\n      element.className = buttonClass;\n      element.innerHTML = buttonContent;\n      var elementWrapper = document.createElement('div');\n      elementWrapper.appendChild(element);\n      return elementWrapper.innerHTML;\n    }\n  }, {\n    key: \"render\",\n    value: function render() {\n      var _this$props = this.props,\n          id = _this$props.id,\n          atts = _this$props.atts,\n          editor = _this$props.editor;\n      var customClass = atts.customClass,\n          alignment = atts.alignment,\n          metaCustomId = atts.metaCustomId;\n      var classes = 'vce-tweet-button';\n      var innerClasses = 'vce-tweet-button-inner';\n      var wrapperClasses = 'vce-tweet-button-wrapper vce';\n      var customProps = {};\n\n      if (typeof customClass === 'string' && customClass) {\n        classes += ' ' + customClass;\n      }\n\n      if (alignment) {\n        classes += \" vce-tweet-button--align-\".concat(alignment);\n      }\n\n      if (metaCustomId) {\n        customProps.id = metaCustomId;\n      }\n\n      var doAll = this.applyDO('all');\n      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_7__.createElement(\"div\", (0,_babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0__[\"default\"])({}, customProps, {\n        className: classes\n      }, editor), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_7__.createElement(\"div\", (0,_babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0__[\"default\"])({\n        className: wrapperClasses,\n        id: 'el-' + id\n      }, doAll), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_7__.createElement(\"div\", {\n        className: innerClasses\n      })));\n    }\n  }]);\n\n  return TwitterButton;\n}(vcvAPI.elementComponent);\n\n\n\n//# sourceURL=webpack:///./twitterButton/component.js?");

/***/ }),

/***/ "./twitterButton/index.js":
/*!********************************!*\
  !*** ./twitterButton/index.js ***!
  \********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vc_cake__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vc-cake */ \"./node_modules/vc-cake/index.js\");\n/* harmony import */ var vc_cake__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vc_cake__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _component__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./component */ \"./twitterButton/component.js\");\n/* eslint-disable import/no-webpack-loader-syntax */\n\n\nvar vcvAddElement = vc_cake__WEBPACK_IMPORTED_MODULE_0___default().getService('cook').add;\nvcvAddElement(__webpack_require__(/*! ./settings.json */ \"./twitterButton/settings.json\"), // Component callback\nfunction (component) {\n  component.add(_component__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n}, // css settings // css for element\n{\n  css: __webpack_require__(/*! raw-loader!./styles.css */ \"./node_modules/raw-loader/index.js!./twitterButton/styles.css\"),\n  editorCss: __webpack_require__(/*! raw-loader!./editor.css */ \"./node_modules/raw-loader/index.js!./twitterButton/editor.css\")\n}, '');\n\n//# sourceURL=webpack:///./twitterButton/index.js?");

/***/ }),

/***/ "./node_modules/raw-loader/index.js!./twitterButton/editor.css":
/*!*************************************************************************!*\
  !*** ../../node_modules/raw-loader/index.js!./twitterButton/editor.css ***!
  \*************************************************************************/
/***/ (function(module) {

eval("module.exports = \"[data-vcv-element-disable-interaction=\\\"true\\\"] .vce-tweet-button-inner {\\n  position: relative;\\n}\\n\\n[data-vcv-element-disable-interaction=\\\"true\\\"] .vce-tweet-button-inner::after {\\n  content: \\\"\\\";\\n  position: absolute;\\n  top: 0;\\n  right: 0;\\n  bottom: 0;\\n  left: 0;\\n  z-index: 999;\\n}\\n\\n.vce-tweet-button {\\n  min-height: 1em;\\n}\\n\"\n\n//# sourceURL=webpack:///./twitterButton/editor.css?../../node_modules/raw-loader/index.js");

/***/ }),

/***/ "./node_modules/raw-loader/index.js!./twitterButton/styles.css":
/*!*************************************************************************!*\
  !*** ../../node_modules/raw-loader/index.js!./twitterButton/styles.css ***!
  \*************************************************************************/
/***/ (function(module) {

eval("module.exports = \".vce-tweet-button {\\n  line-height: 1;\\n}\\n\\n.vce-tweet-button-wrapper {\\n  display: inline-block;\\n}\\n\\n.vce-tweet-button iframe {\\n  display: block;\\n  vertical-align: top;\\n}\\n\\n.vce-tweet-button--align-center {\\n  text-align: center;\\n}\\n\\n.vce-tweet-button--align-right {\\n  text-align: right;\\n}\\n\\n.vce-tweet-button--align-left {\\n  text-align: left;\\n}\\n\\n.vce-tweet-button-inner {\\n  vertical-align: top;\\n  display: inline-block;\\n}\\n\"\n\n//# sourceURL=webpack:///./twitterButton/styles.css?../../node_modules/raw-loader/index.js");

/***/ }),

/***/ "./twitterButton/settings.json":
/*!*************************************!*\
  !*** ./twitterButton/settings.json ***!
  \*************************************/
/***/ (function(module) {

"use strict";
eval("module.exports = JSON.parse('{\"designOptions\":{\"type\":\"designOptions\",\"access\":\"public\",\"value\":{},\"options\":{\"label\":\"Design Options\"}},\"editFormTab1\":{\"type\":\"group\",\"access\":\"protected\",\"value\":[\"buttonType\",\"shareText\",\"tweetText\",\"tweetAccount\",\"hashtagTopic\",\"username\",\"showUsername\",\"tweetButtonSize\",\"alignment\",\"metaCustomId\",\"customClass\"],\"options\":{\"label\":\"General\"}},\"metaEditFormTabs\":{\"type\":\"group\",\"access\":\"protected\",\"value\":[\"editFormTab1\",\"designOptions\"]},\"relatedTo\":{\"type\":\"group\",\"access\":\"protected\",\"value\":[\"General\"]},\"customClass\":{\"type\":\"string\",\"access\":\"public\",\"value\":\"\",\"options\":{\"label\":\"Extra class name\",\"description\":\"Add an extra class name to the element and refer to it from the custom CSS option.\"}},\"buttonType\":{\"type\":\"dropdown\",\"access\":\"public\",\"value\":\"share\",\"options\":{\"label\":\"Button type\",\"values\":[{\"label\":\"Share Button\",\"value\":\"share\"},{\"label\":\"Follow Button\",\"value\":\"follow\"},{\"label\":\"Mention Button\",\"value\":\"mention\"},{\"label\":\"Hashtag Button\",\"value\":\"hashtag\"}]}},\"shareText\":{\"type\":\"string\",\"access\":\"public\",\"value\":\"\",\"options\":{\"label\":\"Tweet text\",\"description\":\"Add custom tweet text or leave empty to use auto-suggested. The link to the page will be added automatically.\",\"dynamicField\":true,\"onChange\":{\"rules\":{\"buttonType\":{\"rule\":\"value\",\"options\":{\"value\":\"share\"}}},\"actions\":[{\"action\":\"toggleVisibility\"}]}}},\"tweetText\":{\"type\":\"string\",\"access\":\"public\",\"value\":\"\",\"options\":{\"label\":\"Tweet text\",\"dynamicField\":true,\"onChange\":{\"rules\":{\"buttonType\":{\"rule\":\"valueIn\",\"options\":{\"values\":[\"mention\",\"hashtag\"]}}},\"actions\":[{\"action\":\"toggleVisibility\"}]}}},\"tweetAccount\":{\"type\":\"string\",\"access\":\"public\",\"value\":\"\",\"options\":{\"label\":\"Recommend Account (@username)\",\"description\":\"Adds via @username at the end of the tweet.\",\"dynamicField\":true,\"onChange\":{\"rules\":{\"buttonType\":{\"rule\":\"value\",\"options\":{\"value\":\"share\"}}},\"actions\":[{\"action\":\"toggleVisibility\"}]}}},\"hashtagTopic\":{\"type\":\"string\",\"access\":\"public\",\"value\":\"#madeinvc\",\"options\":{\"label\":\"Paste a hashtag URL or #hashtag\",\"dynamicField\":true,\"onChange\":{\"rules\":{\"buttonType\":{\"rule\":\"value\",\"options\":{\"value\":\"hashtag\"}}},\"actions\":[{\"action\":\"toggleVisibility\"}]}}},\"username\":{\"type\":\"string\",\"access\":\"public\",\"value\":\"VisualComposers\",\"options\":{\"label\":\"Paste a profile URL or @username\",\"dynamicField\":true,\"onChange\":{\"rules\":{\"buttonType\":{\"rule\":\"valueIn\",\"options\":{\"values\":[\"follow\",\"mention\"]}}},\"actions\":[{\"action\":\"toggleVisibility\"}]}}},\"tweetButtonSize\":{\"type\":\"dropdown\",\"access\":\"public\",\"value\":\"normal\",\"options\":{\"label\":\"Size\",\"values\":[{\"label\":\"Normal\",\"value\":\"normal\"},{\"label\":\"Large\",\"value\":\"large\"}]}},\"alignment\":{\"type\":\"buttonGroup\",\"access\":\"public\",\"value\":\"left\",\"options\":{\"label\":\"Alignment\",\"values\":[{\"label\":\"Left\",\"value\":\"left\",\"icon\":\"vcv-ui-icon-attribute-alignment-left\"},{\"label\":\"Center\",\"value\":\"center\",\"icon\":\"vcv-ui-icon-attribute-alignment-center\"},{\"label\":\"Right\",\"value\":\"right\",\"icon\":\"vcv-ui-icon-attribute-alignment-right\"}]}},\"showUsername\":{\"type\":\"toggle\",\"access\":\"public\",\"value\":true,\"options\":{\"label\":\"Show username\",\"onChange\":{\"rules\":{\"buttonType\":{\"rule\":\"value\",\"options\":{\"value\":\"follow\"}}},\"actions\":[{\"action\":\"toggleVisibility\"}]}}},\"metaDisableInteractionInEditor\":{\"type\":\"toggle\",\"access\":\"protected\",\"value\":true},\"metaCustomId\":{\"type\":\"customId\",\"access\":\"public\",\"value\":\"\",\"options\":{\"label\":\"Element ID\",\"description\":\"Apply a unique ID to the element to link it directly by using #your_id (for element ID use lowercase input only).\"}},\"tag\":{\"access\":\"protected\",\"type\":\"string\",\"value\":\"twitterButton\"}}');\n\n//# sourceURL=webpack:///./twitterButton/settings.json?");

/***/ })

},[['./twitterButton/index.js']]]);