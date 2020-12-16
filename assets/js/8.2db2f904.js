(window.webpackJsonp=window.webpackJsonp||[]).push([[8],{440:function(e,t,o){"use strict";o.r(t);var n=o(52),i=Object(n.a)({},(function(){var e=this,t=e.$createElement,o=e._self._c||t;return o("ContentSlotsDistributor",{attrs:{"slot-key":e.$parent.slotKey}},[o("h1",{attrs:{id:"how-it-works"}},[o("a",{staticClass:"header-anchor",attrs:{href:"#how-it-works"}},[e._v("#")]),e._v(" How it works")]),e._v(" "),o("p",[o("img",{attrs:{src:"https://img.shields.io/github/v/release/tpg/attache?style=flat-square",alt:""}})]),e._v(" "),o("p",[e._v("Attaché makes a number of assumtions around how your environment is set up. Attaché is not as flexible as other more generic deployment tools, but in turn makes configuration a lot easier and much quicker. Attaché deployments are zero-downtime, meaning you don't need to take your application offline at all when deploying a new release.")]),e._v(" "),o("ul",[o("li",[e._v("You must be able to access your server via SSH using a private key. Attaché does not support password authentication.")]),e._v(" "),o("li",[e._v("Your choice of web server muts be configured to serve symbolic links.")])]),e._v(" "),o("p",[e._v("Once you have these items sorted, it's fairly simple to get Attaché running. See "),o("RouterLink",{attrs:{to:"/started/"}},[e._v("Getting Started")]),e._v(" if you're ready to jump right in.")],1),e._v(" "),o("h2",{attrs:{id:"zero-downtime"}},[o("a",{staticClass:"header-anchor",attrs:{href:"#zero-downtime"}},[e._v("#")]),e._v(" Zero Downtime")]),e._v(" "),o("p",[e._v("Attaché is a zero downtime deployment tool meaning that users don't experience any downtime between deployments. This is achieved through the use of symbolic links to serve different directories (releases). Each time you complete a deployment, the final step is to replace the symbolic link that the web server is configured to serve.")]),e._v(" "),o("ol",[o("li",[e._v("Build CSS and JS assets by running "),o("code",[e._v("yarn prod")]),e._v(".")]),e._v(" "),o("li",[e._v("Clone the application repository onto the server into a new directory.")]),e._v(" "),o("li",[e._v("Create a "),o("code",[e._v("storage")]),e._v(" directory outside of the newly cloned repo.")]),e._v(" "),o("li",[e._v("Create a "),o("code",[e._v(".env")]),e._v(" file outside of the newly cloned repo.")]),e._v(" "),o("li",[e._v("Symlink the "),o("code",[e._v("storage")]),e._v(" directory into the new repo.")]),e._v(" "),o("li",[e._v("Symlink the "),o("code",[e._v(".env")]),e._v(" file into the new repo.")]),e._v(" "),o("li",[e._v("Install Composer dependencies on the server with "),o("code",[e._v("composer install")]),e._v(".")]),e._v(" "),o("li",[e._v("Copy the compiled assets into the "),o("code",[e._v("public/js")]),e._v(" and "),o("code",[e._v("public/css")]),e._v(" directories on the server.")]),e._v(" "),o("li",[e._v("Create a symlink to the new repo.")])]),e._v(" "),o("p",[e._v("The resulting directory structure on the server should look something like this:")]),e._v(" "),o("div",{staticClass:"language- extra-class"},[o("pre",{pre:!0,attrs:{class:"language-text"}},[o("code",[e._v("/path/to/application\n|\n+- .env\n|\n+- storage\n|\n+- releases\n|  |\n|  +- cloned_repo\n|     |\n|     +- .env -> /path/to/application/.env\n|     |\n|     +- storage -> /path/to/application/storage\n|     |\n|     +- public\n|\n+- live -> /path/to/application/releases/cloned_repo\n")])])]),o("p",[e._v("Your web server should be configured to serve "),o("code",[e._v("/path/to/application/live/public")]),e._v(".")]),e._v(" "),o("p",[e._v("Each time you deploy again, you Attaché will clone the repo into a new directory inside the "),o("code",[e._v("releases")]),e._v(" directory and replacee the "),o("code",[e._v("live")]),e._v(" symlink. The web server doesn't know any better.")])])}),[],!1,null,null,null);t.default=i.exports}}]);