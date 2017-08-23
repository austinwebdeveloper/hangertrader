<?php
/**
 * @version   $Id: index.php 24174 2014-11-20 15:38:43Z arifin $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

/**
 * @var $layout     RokSprocket_Layout_Strips
 * @var $items      RokSprocket_Item[]
 * @var $parameters RokCommon_Registry
 * @var $pages      int
 */

?>
<div id="sprocket-strips-xscroll" class="sprocket-strips sprocket-strips-xscroll" data-strips="<?php echo $parameters->get('module_id'); ?>">
	<div class="sprocket-strips-xscroll-overlay"><div class="css-loader-wrapper"><div class="css-loader"></div></div></div>

	<div class="viewport">
		<div class="overview">
			<ul class="sprocket-strips-xscroll-container cols-<?php echo $parameters->get('strips_items_per_row'); ?>" data-strips-items>
				<?php
					$index = 0;
					foreach ($items as $item){
						echo $layout->getThemeContext()->load('item.php', array('item'=> $item,'parameters'=>$parameters,'index'=>$index));
						$index++;
					}
				?>
			</ul>
		</div>
	</div>

	<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>

	<div class="sprocket-strips-xscroll-nav">
		<div class="sprocket-strips-xscroll-pagination<?php echo !$parameters->get('strips_show_pagination') || $pages <= 1 ? '-hidden' : '';?>">
			<ul>
			<?php for ($i = 1, $l = $pages;$i <= $pages;$i++): ?>
				<?php
					$class = ($i == 1) ? ' class="active"' : '';
				?>
		    	<li<?php echo $class; ?> data-strips-page="<?php echo $i; ?>"><span><?php echo $i; ?></span></li>
			<?php endfor; ?>
			</ul>
		</div>
		<?php if ($parameters->get('strips_show_arrows')!='hide' && $pages > 1) : ?>
		<div class="sprocket-strips-xscroll-arrows">
			<span class="arrow next" data-strips-next></span>
			<span class="arrow prev" data-strips-previous></span>
		</div>
		<?php endif; ?>
	</div>
</div>

<script type="text/javascript">
	!function(a,b){"use strict";function c(){for(var a=1;a<arguments.length;a++)for(var b in arguments[a])arguments[a].hasOwnProperty(b)&&(arguments[0][b]=arguments[a][b]);return arguments[0]}function d(d,g){function h(){return o.update(),j(),o}function i(){u.style[A]=o.contentPosition/o.trackRatio+"px",r.style[A]=-o.contentPosition+"px",s.style[z]=o.trackSize+"px",t.style[z]=o.trackSize+"px",u.style[z]=o.thumbSize+"px"}function j(){x?q.ontouchstart=function(a){1===a.touches.length&&(k(a.touches[0]),a.stopPropagation())}:(u.onmousedown=k,t.onmousedown=m),a.addEventListener("resize",function(){o.update("relative")},!0),o.options.wheel&&a.addEventListener?d.addEventListener(y,l,!1):o.options.wheel&&(d.onmousewheel=l)}function k(a){p.className+=" noSelect",v=w?a.pageX:a.pageY,o.thumbPosition=parseInt(u.style[A],10)||0,x?(document.ontouchmove=function(a){a.preventDefault(),m(a.touches[0])},document.ontouchend=n):(document.onmousemove=m,document.onmouseup=u.onmouseup=n)}function l(b){if(o.contentRatio<1){var c=b||a.event,e="delta"+o.options.axis.toUpperCase(),f=-(c[e]||c.detail||-1/3*c.wheelDelta)/40;o.contentPosition-=f*o.options.wheelSpeed,o.contentPosition=Math.min(o.contentSize-o.viewportSize,Math.max(0,o.contentPosition)),d.dispatchEvent(B),u.style[A]=o.contentPosition/o.trackRatio+"px",r.style[A]=-o.contentPosition+"px",(o.options.wheelLock||o.contentPosition!==o.contentSize-o.viewportSize&&0!==o.contentPosition)&&c.preventDefault()}}function m(a){if(o.contentRatio<1){var b=w?a.pageX:a.pageY,c=b-v;o.options.scrollInvert&&x&&(c=v-b);var e=Math.min(o.trackSize-o.thumbSize,Math.max(0,o.thumbPosition+c));o.contentPosition=e*o.trackRatio,d.dispatchEvent(B),u.style[A]=e+"px",r.style[A]=-o.contentPosition+"px"}}function n(){p.className=p.className.replace(" noSelect",""),document.onmousemove=document.onmouseup=null,u.onmouseup=null,document.ontouchmove=document.ontouchend=null}this.options=c({},f,g),this._defaults=f,this._name=e;var o=this,p=document.querySelectorAll("body")[0],q=d.querySelectorAll(".viewport")[0],r=d.querySelectorAll(".overview")[0],s=d.querySelectorAll(".scrollbar")[0],t=s.querySelectorAll(".track")[0],u=s.querySelectorAll(".thumb")[0],v=0,w="x"===this.options.axis,x="ontouchstart"in document.documentElement,y="onwheel"in document||document.documentMode>=9?"wheel":document.onmousewheel!==b?"mousewheel":"DOMMouseScroll",z=w?"width":"height",A=w?"left":"top",B=document.createEvent("HTMLEvents");return B.initEvent("move",!0,!0),this.contentPosition=0,this.viewportSize=0,this.contentSize=0,this.contentRatio=0,this.trackSize=0,this.trackRatio=0,this.thumbSize=0,this.thumbPosition=0,this.update=function(a){var b=z.charAt(0).toUpperCase()+z.slice(1).toLowerCase();this.viewportSize=q["offset"+b],this.contentSize=r["scroll"+b],this.contentRatio=this.viewportSize/this.contentSize,this.trackSize=this.options.trackSize||this.viewportSize,this.thumbSize=Math.min(this.trackSize,Math.max(0,this.options.thumbSize||this.trackSize*this.contentRatio)),this.trackRatio=this.options.thumbSize?(this.contentSize-this.viewportSize)/(this.trackSize-this.thumbSize):this.contentSize/this.trackSize,v=t.offsetTop;var c=s.className;switch(s.className=this.contentRatio>=1?c+" disable":c.replace(" disable",""),a){case"bottom":this.contentPosition=this.contentSize-this.viewportSize;break;case"relative":this.contentPosition=Math.min(this.contentSize-this.viewportSize,Math.max(0,this.contentPosition));break;default:this.contentPosition=parseInt(a,10)||0}i()},h()}var e="tinyscrollbar",f={axis:"x",wheel:0,wheelSpeed:40,wheelLock:!0,scrollInvert:!1,trackSize:!1,thumbSize:!1},g=function(a,b){return new d(a,b)};"function"==typeof define&&define.amd?define(function(){return g}):"object"==typeof module&&module.exports?module.exports=g:a.tinyscrollbar=g}(window);

        window.onload = function()
        {
            var $scrollbar = document.getElementById("sprocket-strips-xscroll")
            ,   scrollbar  = tinyscrollbar($scrollbar)
            ;
        }
</script>