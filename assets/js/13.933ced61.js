(window.webpackJsonp=window.webpackJsonp||[]).push([[13],{441:function(t,e,a){"use strict";a.r(e);var s=a(52),o=Object(s.a)({},(function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("ContentSlotsDistributor",{attrs:{"slot-key":t.$parent.slotKey}},[a("h1",{attrs:{id:"getting-started"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#getting-started"}},[t._v("#")]),t._v(" Getting Started")]),t._v(" "),a("p",[a("img",{attrs:{src:"https://img.shields.io/github/v/release/tpg/attache?style=flat-square",alt:""}})]),t._v(" "),a("div",{staticClass:"custom-block warning"},[a("p",{staticClass:"custom-block-title"},[t._v("WARNING")]),t._v(" "),a("p",[t._v("Attaché requires "),a("strong",[t._v("PHP v7.4")]),t._v(" locally. There is no version requirement on the server except what is required by your application, however Attaché only supports Laravel 6 or newer and is only tested on the current major release.")])]),t._v(" "),a("h2",{attrs:{id:"global-installation"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#global-installation"}},[t._v("#")]),t._v(" Global installation")]),t._v(" "),a("p",[t._v("You should install Attaché globally using composer:")]),t._v(" "),a("div",{staticClass:"language- extra-class"},[a("pre",{pre:!0,attrs:{class:"language-text"}},[a("code",[t._v("composer global require thepublicgood/attache\n")])])]),a("p",[t._v("Make sure that "),a("code",[t._v("~/.composer/vendor/bin")]),t._v(" is in your path. Now you should have access to Attache from anywhere on your composer through the "),a("code",[t._v("attache")]),t._v(" command.")]),t._v(" "),a("h2",{attrs:{id:"git-repository-required"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#git-repository-required"}},[t._v("#")]),t._v(" Git repository required")]),t._v(" "),a("p",[t._v("Attaché assumes that your project has already been imported into a Git repository. If not, then you'll need to do that first. Attaché will clone the repository during deployment.")]),t._v(" "),a("p",[t._v("Attaché will also run a "),a("code",[t._v("yarn prod")]),t._v(" before doing the actual deployment to create compiled assets. You'll want to make sure you're not commiting those compiled assets to your repository. For most Laravel applications your "),a("code",[t._v(".gitignore")]),t._v(" file should include:")]),t._v(" "),a("div",{staticClass:"language- extra-class"},[a("pre",{pre:!0,attrs:{class:"language-text"}},[a("code",[t._v("public/js/\npublic/css/\npublic/mix-manifest.json\n")])])]),a("p",[t._v("Or place a "),a("code",[t._v(".gitignore file in")]),t._v("public/js"),a("code",[t._v("and")]),t._v("public/css` with the following content:")]),t._v(" "),a("div",{staticClass:"language- extra-class"},[a("pre",{pre:!0,attrs:{class:"language-text"}},[a("code",[t._v("!.gitignore\n*\n")])])]),a("p",[t._v("This will ensure that the "),a("code",[t._v("js")]),t._v(" and "),a("code",[t._v("css")]),t._v(" directories are at least created when cloing the repository.")]),t._v(" "),a("h2",{attrs:{id:"initialize-a-project"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#initialize-a-project"}},[t._v("#")]),t._v(" Initialize a project")]),t._v(" "),a("p",[t._v("Initialize Attaché in your Laravel project by running the following in your project root:")]),t._v(" "),a("div",{staticClass:"language- extra-class"},[a("pre",{pre:!0,attrs:{class:"language-text"}},[a("code",[t._v("attache init\n")])])]),a("p",[t._v("The "),a("code",[t._v("init")]),t._v(" command is usually non-interactive and will create a basic config file named "),a("code",[t._v(".attache.json")]),t._v(" in your project root. The command will attempt to discover the remote Git URL and add it to your config file automatically. If you have more than one Git remote, the "),a("code",[t._v("init")]),t._v(" command will give you the option to choose before creating the config file.")]),t._v(" "),a("p",[t._v("The initial config file looks something like this:")]),t._v(" "),a("div",{staticClass:"language-json extra-class"},[a("pre",{pre:!0,attrs:{class:"language-json"}},[a("code",[a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"repository"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"git@repository.git"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"servers"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n        "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"production"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n            "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"name"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"production"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n            "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"host"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"example.test"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n            "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"port"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token number"}},[t._v("22")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n            "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"user"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"user"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n            "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"root"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"/path/to/application"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n            "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"paths"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n                "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"releases"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"releases"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n                "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"serve"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"live"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n                "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"storage"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"storage"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n                "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"env"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('".env"')]),t._v("\n            "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n            "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"php"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n                "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"bin"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"php"')]),t._v("\n            "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n            "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"composer"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n                "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"bin"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"composer"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n                "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"local"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token boolean"}},[t._v("false")]),t._v("\n            "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n            "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"assets"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n                "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"public/js"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"public/js"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n                "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"public/css"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"public/css"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n                "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"public/mix-manifest.json"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"public/mix-manifest.json"')]),t._v("\n            "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n            "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"branch"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"master"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n            "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"migrate"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token boolean"}},[t._v("false")]),t._v("\n        "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),t._v("\n")])])]),a("p",[t._v("If, for some reason you want to use a different filename, you can use the "),a("code",[t._v("--filename")]),t._v(" option of the "),a("code",[t._v("init")]),t._v(" command.")]),t._v(" "),a("div",{staticClass:"language- extra-class"},[a("pre",{pre:!0,attrs:{class:"language-text"}},[a("code",[t._v("attache init --filename=attache-config.json\n")])])]),a("p",[t._v("The other Attaché commands will not know how to find the renamed configuration file, you will need to supply a "),a("code",[t._v("--config")]),t._v(" option with each one.")]),t._v(" "),a("div",{staticClass:"language- extra-class"},[a("pre",{pre:!0,attrs:{class:"language-text"}},[a("code",[t._v("attache deploy production --config=attache-config.json\n")])])]),a("h2",{attrs:{id:"safety-first"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#safety-first"}},[t._v("#")]),t._v(" Safety first")]),t._v(" "),a("p",[t._v("It's recommended that you add "),a("code",[t._v(".attache.json")]),t._v(" to your .gitignore file unless there is very good reason to have it in your repository. This will ensure you don't potentially commit sensitive details about your environment into a publicly accessible repository. Instead, you could keep a copy of your config locally. If you ever loose the config file, it's simple enough to recreate.")]),t._v(" "),a("p",[t._v("If committing the config file to your repository, ensure that it is private and that your server is properly secured.")]),t._v(" "),a("h2",{attrs:{id:"configuration"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#configuration"}},[t._v("#")]),t._v(" Configuration")]),t._v(" "),a("p",[t._v("Open the "),a("code",[t._v(".attache.json")]),t._v(" file in your editor. You'll need update the server configuration to reflect your environment. You can configure as many servers as you need. Each server describes a deployment target and can have "),a("code",[t._v("host")]),t._v(", "),a("code",[t._v("port")]),t._v(", "),a("code",[t._v("user")]),t._v(", "),a("code",[t._v("root")]),t._v(" and "),a("code",[t._v("brach")]),t._v(" settings.")]),t._v(" "),a("table",[a("thead",[a("tr",[a("th",[t._v("Setting")]),t._v(" "),a("th",[t._v("Description")])])]),t._v(" "),a("tbody",[a("tr",[a("td",[a("code",[t._v("host")])]),t._v(" "),a("td",[t._v("The hostname or IP address of the server.")])]),t._v(" "),a("tr",[a("td",[a("code",[t._v("port")])]),t._v(" "),a("td",[t._v("The SSH port. Usually 22.")])]),t._v(" "),a("tr",[a("td",[a("code",[t._v("user")])]),t._v(" "),a("td",[t._v("The user that Attaché can log in as to deploy your application.")])]),t._v(" "),a("tr",[a("td",[a("code",[t._v("root")])]),t._v(" "),a("td",[t._v("The path to the deployment root (see directory structure below).")])]),t._v(" "),a("tr",[a("td",[a("code",[t._v("branch")])]),t._v(" "),a("td",[t._v("The Git branch to clone from.")])])])]),t._v(" "),a("div",{staticClass:"custom-block tip"},[a("p",{staticClass:"custom-block-title"},[t._v("Note")]),t._v(" "),a("p",[t._v("Attaché does not support password authentication and you MUST be able to log in to the server as the specified user using a public-private key.")])]),t._v(" "),a("h2",{attrs:{id:"directory-structure-on-the-server"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#directory-structure-on-the-server"}},[t._v("#")]),t._v(" Directory structure on the server")]),t._v(" "),a("p",[t._v("When your application is deployed for the first time, Attaché will create a new project in the root path you specify in the config file. A new directory named "),a("code",[t._v("releases")]),t._v(" will be created to contain the application deployments. A "),a("code",[t._v("storage")]),t._v(" directory will be created which will be a reflection of your applications "),a("code",[t._v("storage")]),t._v(" directory. Lastly, a "),a("code",[t._v(".env")]),t._v(" file will also be placed in the root directory which will be your applications "),a("code",[t._v(".env")]),t._v(" file. To make your application live a symbolic link named "),a("code",[t._v("live")]),t._v(" will be created that points to "),a("code",[t._v("releases/{release_id}")]),t._v(".")]),t._v(" "),a("p",[t._v("Once deployed, your application structure will look a bit like this:")]),t._v(" "),a("div",{staticClass:"language- extra-class"},[a("pre",{pre:!0,attrs:{class:"language-text"}},[a("code",[t._v("/project/root\n|\n+- storage\n|\n+- .env\n|\n+- releases\n|   |\n|   +- release_id\n|       |\n|       +- public\n|       |\n|       +- storage -> /project/root/storage\n|       |\n|       +- .env -> /project/root/.env\n|\n+- live -> /project/root/releases/release_id\n")])])]),a("h2",{attrs:{id:"update-your-web-server"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#update-your-web-server"}},[t._v("#")]),t._v(" Update your web server")]),t._v(" "),a("p",[t._v("In order for this to work, you'll need to update your web server to serve the "),a("code",[t._v("live")]),t._v(" symbolic link. If you're using Nginx, you don't need to change anything. Just set "),a("code",[t._v("root")]),t._v(" to point to the symlink. If you're using Apache, you might need add something like "),a("code",[t._v("+options FollowSymLinks")]),t._v(" to your server config to get it to actually serve the symbolic link.")]),t._v(" "),a("h2",{attrs:{id:"server-env-file"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#server-env-file"}},[t._v("#")]),t._v(" Server "),a("code",[t._v(".env")]),t._v(" file")]),t._v(" "),a("p",[t._v("This step is optional, but it makes set up a little easier. Create a copy of your "),a("code",[t._v(".env")]),t._v(" file as "),a("code",[t._v("attache.env")]),t._v(":")]),t._v(" "),a("div",{staticClass:"language- extra-class"},[a("pre",{pre:!0,attrs:{class:"language-text"}},[a("code",[t._v("cp .env attache.env\n")])])]),a("p",[t._v("Change the content of the new "),a("code",[t._v("attache.env")]),t._v(" file to match how it would look on the server. If you don't do this, Attaché will use the content of the "),a("code",[t._v(".env.example")]),t._v(" file but will automatically set "),a("code",[t._v("APP_ENV=production")]),t._v(" and "),a("code",[t._v("APP_DEBUG=false")]),t._v(".")]),t._v(" "),a("h2",{attrs:{id:"first-deployment"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#first-deployment"}},[t._v("#")]),t._v(" First deployment")]),t._v(" "),a("p",[t._v("Before running your first deployment, we'll assume that you have already created a database schema for your application and you've updated the "),a("code",[t._v("attache.env")]),t._v(" file. If you want, you can also add the "),a("code",[t._v("migrate")]),t._v(" option to your server config and set it to "),a("code",[t._v("true")]),t._v(". This will migrate your database for you.")]),t._v(" "),a("div",{staticClass:"language-json extra-class"},[a("pre",{pre:!0,attrs:{class:"language-json"}},[a("code",[a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"host"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"example.test"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token comment"}},[t._v("//...")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"migrate"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token boolean"}},[t._v("true")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),t._v("\n")])])]),a("p",[t._v("Once you have the set up tasks complete, you can deploy your application for the first time. Usually when deploying, you'll use the "),a("code",[t._v("attache deploy")]),t._v(" command, but since this is the first one, you'll need to use the "),a("code",[t._v("attache install")]),t._v(" command. This takes a few extra steps (like placing the "),a("code",[t._v(".env")]),t._v(" file and the "),a("code",[t._v("storage")]),t._v(" directory) that would not normally be done during a normal deployment. If you created an "),a("code",[t._v("attache.env")]),t._v(" file, specify it with the "),a("code",[t._v("--env")]),t._v(" command-line attribute.")]),t._v(" "),a("div",{staticClass:"language- extra-class"},[a("pre",{pre:!0,attrs:{class:"language-text"}},[a("code",[t._v("attache install --env=attache.env\n")])])]),a("p",[t._v("Deployment usually takes a few seconds to a few minutes depending on the complexity if your application and build tasks.")]),t._v(" "),a("h2",{attrs:{id:"conclusion"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#conclusion"}},[t._v("#")]),t._v(" Conclusion")]),t._v(" "),a("p",[t._v("If all goes well, your application should now be deployed to the server and accessible via your websites URL. Attaché provides a number of useful configuration options which can help to solve more complex deployment scenarios. Take a look at the "),a("RouterLink",{attrs:{to:"/reference/"}},[t._v("configuration reference")]),t._v(" for more details.")],1)])}),[],!1,null,null,null);e.default=o.exports}}]);