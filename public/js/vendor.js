(function(root){var setTimeoutFunc=setTimeout;function noop(){}function bind(fn,thisArg){return function(){fn.apply(thisArg,arguments);};}function Promise(fn){if(typeof this!=="object"){throw new TypeError("Promises must be constructed via new");}if(typeof fn!=="function"){throw new TypeError("not a function");}this._state=0;this._handled=false;this._value=undefined;this._deferreds=[];doResolve(fn,this);}function handle(self,deferred){while(self._state===3){self=self._value;}if(self._state===0){self._deferreds.push(deferred);return;}self._handled=true;Promise._immediateFn(function(){var cb=self._state===1?deferred.onFulfilled:deferred.onRejected;if(cb===null){(self._state===1?resolve:reject)(deferred.promise,self._value);return;}var ret;try{ret=cb(self._value);}catch(e){reject(deferred.promise,e);return;}resolve(deferred.promise,ret);});}function resolve(self,newValue){try{if(newValue===self){throw new TypeError("A promise cannot be resolved with itself.");}if(newValue&&(typeof newValue==="object"||typeof newValue==="function")){var then=newValue.then;if(newValue instanceof Promise){self._state=3;self._value=newValue;finale(self);return;}else{if(typeof then==="function"){doResolve(bind(then,newValue),self);return;}}}self._state=1;self._value=newValue;finale(self);}catch(e){reject(self,e);}}function reject(self,newValue){self._state=2;self._value=newValue;finale(self);}function finale(self){if(self._state===2&&self._deferreds.length===0){Promise._immediateFn(function(){if(!self._handled){Promise._unhandledRejectionFn(self._value);}});}for(var i=0,len=self._deferreds.length;i<len;i++){handle(self,self._deferreds[i]);}self._deferreds=null;}function Handler(onFulfilled,onRejected,promise){this.onFulfilled=typeof onFulfilled==="function"?onFulfilled:null;this.onRejected=typeof onRejected==="function"?onRejected:null;this.promise=promise;}function doResolve(fn,self){var done=false;try{fn(function(value){if(done){return;}done=true;resolve(self,value);},function(reason){if(done){return;}done=true;reject(self,reason);});}catch(ex){if(done){return;}done=true;reject(self,ex);}}Promise.prototype["catch"]=function(onRejected){return this.then(null,onRejected);};Promise.prototype.then=function(onFulfilled,onRejected){var prom=new (this.constructor)(noop);handle(this,new Handler(onFulfilled,onRejected,prom));return prom;};Promise.all=function(arr){var args=Array.prototype.slice.call(arr);return new Promise(function(resolve,reject){if(args.length===0){return resolve([]);}var remaining=args.length;function res(i,val){try{if(val&&(typeof val==="object"||typeof val==="function")){var then=val.then;if(typeof then==="function"){then.call(val,function(val){res(i,val);},reject);return;}}args[i]=val;if(--remaining===0){resolve(args);}}catch(ex){reject(ex);}}for(var i=0;i<args.length;i++){res(i,args[i]);}});};Promise.resolve=function(value){if(value&&typeof value==="object"&&value.constructor===Promise){return value;}return new Promise(function(resolve){resolve(value);});};Promise.reject=function(value){return new Promise(function(resolve,reject){reject(value);});};Promise.race=function(values){return new Promise(function(resolve,reject){for(var i=0,len=values.length;i<len;i++){values[i].then(resolve,reject);}});};Promise._immediateFn=(typeof setImmediate==="function"&&function(fn){setImmediate(fn);})||function(fn){setTimeoutFunc(fn,0);};Promise._unhandledRejectionFn=function _unhandledRejectionFn(err){if(typeof console!=="undefined"&&console){console.warn("Possible Unhandled Promise Rejection:",err);}};Promise._setImmediateFn=function _setImmediateFn(fn){Promise._immediateFn=fn;};Promise._setUnhandledRejectionFn=function _setUnhandledRejectionFn(fn){Promise._unhandledRejectionFn=fn;};if(typeof module!=="undefined"&&module.exports){module.exports=Promise;}else{if(!root.Promise){root.Promise=Promise;}}})(this);(function(thisVar,undefined){var main=(typeof window==="object"&&window)||(typeof global==="object"&&global)||typeof self==="object"&&self||thisVar;var hasSetImmediate=typeof setImmediate==="function";var hasNextTick=typeof process==="object"&&!!process&&typeof process.nextTick==="function";var index=0;function getNewIndex(){if(index===9007199254740991){return 0;}return ++index;}var setAsap=(function(){var hiddenDiv,scriptEl,timeoutFn,callbacks;if(main.MutationObserver){return function setAsap(callback){hiddenDiv=document.createElement("div");(new MutationObserver(function(){callback();hiddenDiv=null;})).observe(hiddenDiv,{attributes:true});hiddenDiv.setAttribute("i","1");};}else{if(!hasSetImmediate&&main.postMessage&&!main.importScripts&&main.addEventListener){var MESSAGE_PREFIX="com.setImmediate"+Math.random();callbacks={};var onGlobalMessage=function(event){if(event.source===main&&event.data.indexOf(MESSAGE_PREFIX)===0){var i=event.data.split(":")[1];callbacks[i]();delete callbacks[i];}};main.addEventListener("message",onGlobalMessage,false);return function setAsap(callback){var i=getNewIndex();callbacks[i]=callback;main.postMessage(MESSAGE_PREFIX+":"+i,"*");};}else{if(!hasSetImmediate&&main.document&&"onreadystatechange" in document.createElement("script")){return function setAsap(callback){scriptEl=document.createElement("script");scriptEl.onreadystatechange=function onreadystatechange(){scriptEl.onreadystatechange=null;scriptEl.parentNode.removeChild(scriptEl);scriptEl=null;callback();};document.body.appendChild(scriptEl);};}else{timeoutFn=(hasSetImmediate&&setImmediate)||(hasNextTick&&process.nextTick)||setTimeout;return function setAsap(callback){timeoutFn(callback);};}}}})();if(typeof module!=="undefined"&&module.exports){module.exports=setAsap;}else{if(typeof require!=="undefined"&&require.amd){define(function(){return setAsap;});}else{main.setAsap=setAsap;}}})(this);