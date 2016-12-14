THREE.MTLLoader=function(a,b,c){this.baseUrl=a,this.options=b,this.crossOrigin=c},THREE.MTLLoader.prototype={constructor:THREE.MTLLoader,load:function(a,b,c,d){var e=this,f=new THREE.XHRLoader;f.setCrossOrigin(this.crossOrigin),f.load(a,function(a){b(e.parse(a))})},parse:function(a){for(var b=a.split("\n"),c={},d=/\s+/,e={},f=0;f<b.length;f++){var g=b[f];if(g=g.trim(),0!==g.length&&"#"!==g.charAt(0)){var h=g.indexOf(" "),i=h>=0?g.substring(0,h):g;i=i.toLowerCase();var j=h>=0?g.substring(h+1):"";if(j=j.trim(),"newmtl"===i)c={name:j},e[j]=c;else if(c)if("ka"===i||"kd"===i||"ks"===i){var k=j.split(d,3);c[i]=[parseFloat(k[0]),parseFloat(k[1]),parseFloat(k[2])]}else c[i]=j}}var l=new THREE.MTLLoader.MaterialCreator(this.baseUrl,this.options);return l.setMaterials(e),l}},THREE.MTLLoader.MaterialCreator=function(a,b){this.baseUrl=a,this.options=b,this.materialsInfo={},this.materials={},this.materialsArray=[],this.nameLookup={},this.side=this.options&&this.options.side?this.options.side:THREE.FrontSide,this.wrap=this.options&&this.options.wrap?this.options.wrap:THREE.RepeatWrapping},THREE.MTLLoader.MaterialCreator.prototype={constructor:THREE.MTLLoader.MaterialCreator,setMaterials:function(a){this.materialsInfo=this.convert(a),this.materials={},this.materialsArray=[],this.nameLookup={}},convert:function(a){if(!this.options)return a;var b={};for(var c in a){var d=a[c],e={};b[c]=e;for(var f in d){var g=!0,h=d[f],i=f.toLowerCase();switch(i){case"kd":case"ka":case"ks":this.options&&this.options.normalizeRGB&&(h=[h[0]/255,h[1]/255,h[2]/255]),this.options&&this.options.ignoreZeroRGBs&&0===h[0]&&0===h[1]&&0===h[1]&&(g=!1);break;case"d":this.options&&this.options.invertTransparency&&(h=1-h)}g&&(e[i]=h)}}return b},preload:function(){for(var a in this.materialsInfo)this.create(a)},getIndex:function(a){return this.nameLookup[a]},getAsArray:function(){var a=0;for(var b in this.materialsInfo)this.materialsArray[a]=this.create(b),this.nameLookup[b]=a,a++;return this.materialsArray},create:function(a){return void 0===this.materials[a]&&this.createMaterial_(a),this.materials[a]},createMaterial_:function(a){var b=this.materialsInfo[a],c={name:a,side:this.side};for(var d in b){var e=b[d];switch(d.toLowerCase()){case"kd":c.diffuse=(new THREE.Color).fromArray(e);break;case"ka":c.ambient=(new THREE.Color).fromArray(e);break;case"ks":c.specular=(new THREE.Color).fromArray(e);break;case"map_kd":c.map=this.loadTexture(this.baseUrl+e),c.map.wrapS=this.wrap,c.map.wrapT=this.wrap;break;case"ns":c.shininess=e;break;case"d":e<1&&(c.transparent=!0,c.opacity=e)}}return c.diffuse&&(c.ambient||(c.ambient=c.diffuse),c.color=c.diffuse),this.materials[a]=new THREE.MeshPhongMaterial(c),this.materials[a]},loadTexture:function(a,b,c,d){var e=/\.dds$/i.test(a);if(e)var f=THREE.ImageUtils.loadCompressedTexture(a,b,c,d);else{var g=new Image,f=new THREE.Texture(g,b),h=new THREE.ImageLoader;h.crossOrigin=this.crossOrigin,h.load(a,function(a){f.image=THREE.MTLLoader.ensurePowerOfTwo_(a),f.needsUpdate=!0,c&&c(f)})}return f}},THREE.MTLLoader.ensurePowerOfTwo_=function(a){if(!THREE.MTLLoader.isPowerOfTwo_(a.width)||!THREE.MTLLoader.isPowerOfTwo_(a.height)){var b=document.createElement("canvas");b.width=THREE.MTLLoader.nextHighestPowerOfTwo_(a.width),b.height=THREE.MTLLoader.nextHighestPowerOfTwo_(a.height);var c=b.getContext("2d");return c.drawImage(a,0,0,a.width,a.height,0,0,b.width,b.height),b}return a},THREE.MTLLoader.isPowerOfTwo_=function(a){return 0===(a&a-1)},THREE.MTLLoader.nextHighestPowerOfTwo_=function(a){--a;for(var b=1;b<32;b<<=1)a|=a>>b;return a+1},THREE.EventDispatcher.prototype.apply(THREE.MTLLoader.prototype);