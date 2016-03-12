/* ===================================================
 * bootstrap-transition.js v2.3.1
 * http://twitter.github.com/bootstrap/javascript.html#transitions
 * ===================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */


!function ($) {

  "use strict"; // jshint ;_;


  /* CSS TRANSITION SUPPORT (http://www.modernizr.com/)
   * ======================================================= */

  $(function () {

    $.support.transition = (function () {

      var transitionEnd = (function () {

        var el = document.createElement('bootstrap')
          , transEndEventNames = {
               'WebkitTransition' : 'webkitTransitionEnd'
            ,  'MozTransition'    : 'transitionend'
            ,  'OTransition'      : 'oTransitionEnd otransitionend'
            ,  'transition'       : 'transitionend'
            }
          , name

        for (name in transEndEventNames){
          if (el.style[name] !== undefined) {
            return transEndEventNames[name]
          }
        }

      }())

      return transitionEnd && {
        end: transitionEnd
      }

    })()

  })

}(window.jQuery);
/* =========================================================
 * bootstrap-modal.js v2.3.1
 * http://twitter.github.com/bootstrap/javascript.html#modals
 * =========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================= */


!function ($) {

  "use strict"; // jshint ;_;


 /* MODAL CLASS DEFINITION
  * ====================== */

  var Modal = function (element, options) {
    this.options = options
    this.$element = $(element)
      .delegate('[data-dismiss="modal"]', 'click.dismiss.modal', $.proxy(this.hide, this))
    this.options.remote && this.$element.find('.modal-body').load(this.options.remote)
  }

  Modal.prototype = {

      constructor: Modal

    , toggle: function () {
        return this[!this.isShown ? 'show' : 'hide']()
      }

    , show: function () {
        var that = this
          , e = $.Event('show')

        this.$element.trigger(e)

        if (this.isShown || e.isDefaultPrevented()) return

        this.isShown = true

        this.escape()

        this.backdrop(function () {
          var transition = $.support.transition && that.$element.hasClass('fade')

          if (!that.$element.parent().length) {
            that.$element.appendTo(document.body) //don't move modals dom position
          }

          that.$element.show()

          if (transition) {
            that.$element[0].offsetWidth // force reflow
          }

          that.$element
            .addClass('in')
            .attr('aria-hidden', false)

          that.enforceFocus()

          transition ?
            that.$element.one($.support.transition.end, function () { that.$element.focus().trigger('shown') }) :
            that.$element.focus().trigger('shown')

        })
      }

    , hide: function (e) {
        e && e.preventDefault()

        var that = this

        e = $.Event('hide')

        this.$element.trigger(e)

        if (!this.isShown || e.isDefaultPrevented()) return

        this.isShown = false

        this.escape()

        $(document).off('focusin.modal')

        this.$element
          .removeClass('in')
          .attr('aria-hidden', true)

        $.support.transition && this.$element.hasClass('fade') ?
          this.hideWithTransition() :
          this.hideModal()
      }

    , enforceFocus: function () {
        var that = this
        $(document).on('focusin.modal', function (e) {
          if (that.$element[0] !== e.target && !that.$element.has(e.target).length) {
            that.$element.focus()
          }
        })
      }

    , escape: function () {
        var that = this
        if (this.isShown && this.options.keyboard) {
          this.$element.on('keyup.dismiss.modal', function ( e ) {
            e.which == 27 && that.hide()
          })
        } else if (!this.isShown) {
          this.$element.off('keyup.dismiss.modal')
        }
      }

    , hideWithTransition: function () {
        var that = this
          , timeout = setTimeout(function () {
              that.$element.off($.support.transition.end)
              that.hideModal()
            }, 500)

        this.$element.one($.support.transition.end, function () {
          clearTimeout(timeout)
          that.hideModal()
        })
      }

    , hideModal: function () {
        var that = this
        this.$element.hide()
        this.backdrop(function () {
          that.removeBackdrop()
          that.$element.trigger('hidden')
        })
      }

    , removeBackdrop: function () {
        this.$backdrop && this.$backdrop.remove()
        this.$backdrop = null
      }

    , backdrop: function (callback) {
        var that = this
          , animate = this.$element.hasClass('fade') ? 'fade' : ''

        if (this.isShown && this.options.backdrop) {
          var doAnimate = $.support.transition && animate

          this.$backdrop = $('<div class="modal-backdrop ' + animate + '" />')
            .appendTo(document.body)

          this.$backdrop.click(
            this.options.backdrop == 'static' ?
              $.proxy(this.$element[0].focus, this.$element[0])
            : $.proxy(this.hide, this)
          )

          if (doAnimate) this.$backdrop[0].offsetWidth // force reflow

          this.$backdrop.addClass('in')

          if (!callback) return

          doAnimate ?
            this.$backdrop.one($.support.transition.end, callback) :
            callback()

        } else if (!this.isShown && this.$backdrop) {
          this.$backdrop.removeClass('in')

          $.support.transition && this.$element.hasClass('fade')?
            this.$backdrop.one($.support.transition.end, callback) :
            callback()

        } else if (callback) {
          callback()
        }
      }
  }


 /* MODAL PLUGIN DEFINITION
  * ======================= */

  var old = $.fn.modal

  $.fn.modal = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('modal')
        , options = $.extend({}, $.fn.modal.defaults, $this.data(), typeof option == 'object' && option)
      if (!data) $this.data('modal', (data = new Modal(this, options)))
      if (typeof option == 'string') data[option]()
      else if (options.show) data.show()
    })
  }

  $.fn.modal.defaults = {
      backdrop: true
    , keyboard: true
    , show: true
  }

  $.fn.modal.Constructor = Modal


 /* MODAL NO CONFLICT
  * ================= */

  $.fn.modal.noConflict = function () {
    $.fn.modal = old
    return this
  }


 /* MODAL DATA-API
  * ============== */

  $(document).on('click.modal.data-api', '[data-toggle="modal"]', function (e) {
    var $this = $(this)
      , href = $this.attr('href')
      , $target = $($this.attr('data-target') || (href && href.replace(/.*(?=#[^\s]+$)/, ''))) //strip for ie7
      , option = $target.data('modal') ? 'toggle' : $.extend({ remote:!/#/.test(href) && href }, $target.data(), $this.data())

    e.preventDefault()

    $target
      .modal(option)
      .one('hide', function () {
        $this.focus()
      })
  })

}(window.jQuery);


/* ========================================================================
 * Bootstrap: dropdown.js v3.0.3
 * http://getbootstrap.com/javascript/#dropdowns
 * ========================================================================
 * Copyright 2013 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================================== */


+function ($) { "use strict";

  // DROPDOWN CLASS DEFINITION
  // =========================

  var backdrop = '.dropdown-backdrop'
  var toggle   = '[data-toggle=dropdown]'
  var Dropdown = function (element) {
    $(element).on('click.bs.dropdown', this.toggle)
  }

  Dropdown.prototype.toggle = function (e) {
    var $this = $(this)

    if ($this.is('.disabled, :disabled')) return

    var $parent  = getParent($this)
    var isActive = $parent.hasClass('open')

    clearMenus()

    if (!isActive) {
      if ('ontouchstart' in document.documentElement && !$parent.closest('.navbar-nav').length) {
        // if mobile we use a backdrop because click events don't delegate
        $('<div class="dropdown-backdrop"/>').insertAfter($(this)).on('click', clearMenus)
      }

      $parent.trigger(e = $.Event('show.bs.dropdown'))

      if (e.isDefaultPrevented()) return

      $parent
        .toggleClass('open')
        .trigger('shown.bs.dropdown')

      $this.focus()
    }

    return false
  }

  Dropdown.prototype.keydown = function (e) {
    if (!/(38|40|27)/.test(e.keyCode)) return

    var $this = $(this)

    e.preventDefault()
    e.stopPropagation()

    if ($this.is('.disabled, :disabled')) return

    var $parent  = getParent($this)
    var isActive = $parent.hasClass('open')

    if (!isActive || (isActive && e.keyCode == 27)) {
      if (e.which == 27) $parent.find(toggle).focus()
      return $this.click()
    }

    var $items = $('[role=menu] li:not(.divider):visible a', $parent)

    if (!$items.length) return

    var index = $items.index($items.filter(':focus'))

    if (e.keyCode == 38 && index > 0)                 index--                        // up
    if (e.keyCode == 40 && index < $items.length - 1) index++                        // down
    if (!~index)                                      index=0

    $items.eq(index).focus()
  }

  function clearMenus() {
    $(backdrop).remove()
    $(toggle).each(function (e) {
      var $parent = getParent($(this))
      if (!$parent.hasClass('open')) return
      $parent.trigger(e = $.Event('hide.bs.dropdown'))
      if (e.isDefaultPrevented()) return
      $parent.removeClass('open').trigger('hidden.bs.dropdown')
    })
  }

  function getParent($this) {
    var selector = $this.attr('data-target')

    if (!selector) {
      selector = $this.attr('href')
      selector = selector && /#/.test(selector) && selector.replace(/.*(?=#[^\s]*$)/, '') //strip for ie7
    }

    var $parent = selector && $(selector)

    return $parent && $parent.length ? $parent : $this.parent()
  }


  // DROPDOWN PLUGIN DEFINITION
  // ==========================

  var old = $.fn.dropdown

  $.fn.dropdown = function (option) {
    return this.each(function () {
      var $this = $(this)
      var data  = $this.data('bs.dropdown')

      if (!data) $this.data('bs.dropdown', (data = new Dropdown(this)))
      if (typeof option == 'string') data[option].call($this)
    })
  }

  $.fn.dropdown.Constructor = Dropdown


  // DROPDOWN NO CONFLICT
  // ====================

  $.fn.dropdown.noConflict = function () {
    $.fn.dropdown = old
    return this
  }


  // APPLY TO STANDARD DROPDOWN ELEMENTS
  // ===================================

  $(document)
    .on('click.bs.dropdown.data-api', clearMenus)
    .on('click.bs.dropdown.data-api', '.dropdown form', function (e) { e.stopPropagation() })
    .on('click.bs.dropdown.data-api'  , toggle, Dropdown.prototype.toggle)
    .on('keydown.bs.dropdown.data-api', toggle + ', [role=menu]' , Dropdown.prototype.keydown)

}(jQuery);


/* =============================================================
 * bootstrap-scrollspy.js v2.3.1
 * http://twitter.github.com/bootstrap/javascript.html#scrollspy
 * =============================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================== */


!function ($) {

  "use strict"; // jshint ;_;


 /* SCROLLSPY CLASS DEFINITION
  * ========================== */

  function ScrollSpy(element, options) {
    var process = $.proxy(this.process, this)
      , $element = $(element).is('body') ? $(window) : $(element)
      , href
    this.options = $.extend({}, $.fn.scrollspy.defaults, options)
    this.$scrollElement = $element.on('scroll.scroll-spy.data-api', process)
    this.selector = (this.options.target
      || ((href = $(element).attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '')) //strip for ie7
      || '') + ' .nav li > a'
    this.$body = $('body')
    this.refresh()
    this.process()
  }

  ScrollSpy.prototype = {

      constructor: ScrollSpy

    , refresh: function () {
        var self = this
          , $targets

        this.offsets = $([])
        this.targets = $([])

        $targets = this.$body
          .find(this.selector)
          .map(function () {
            var $el = $(this)
              , href = $el.data('target') || $el.attr('href')
              , $href = /^#\w/.test(href) && $(href)
            return ( $href
              && $href.length
              && [[ $href.position().top + (!$.isWindow(self.$scrollElement.get(0)) && self.$scrollElement.scrollTop()), href ]] ) || null
          })
          .sort(function (a, b) { return a[0] - b[0] })
          .each(function () {
            self.offsets.push(this[0])
            self.targets.push(this[1])
          })
      }

    , process: function () {
        var scrollTop = this.$scrollElement.scrollTop() + this.options.offset
          , scrollHeight = this.$scrollElement[0].scrollHeight || this.$body[0].scrollHeight
          , maxScroll = scrollHeight - this.$scrollElement.height()
          , offsets = this.offsets
          , targets = this.targets
          , activeTarget = this.activeTarget
          , i

        if (scrollTop >= maxScroll) {
          return activeTarget != (i = targets.last()[0])
            && this.activate ( i )
        }

        for (i = offsets.length; i--;) {
          activeTarget != targets[i]
            && scrollTop >= offsets[i]
            && (!offsets[i + 1] || scrollTop <= offsets[i + 1])
            && this.activate( targets[i] )
        }
      }

    , activate: function (target) {
        var active
          , selector

        this.activeTarget = target

        $(this.selector)
          .parent('.active')
          .removeClass('active')

        selector = this.selector
          + '[data-target="' + target + '"],'
          + this.selector + '[href="' + target + '"]'

        active = $(selector)
          .parent('li')
          .addClass('active')

        if (active.parent('.dropdown-menu').length)  {
          active = active.closest('li.dropdown').addClass('active')
        }

        active.trigger('activate')
      }

  }


 /* SCROLLSPY PLUGIN DEFINITION
  * =========================== */

  var old = $.fn.scrollspy

  $.fn.scrollspy = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('scrollspy')
        , options = typeof option == 'object' && option
      if (!data) $this.data('scrollspy', (data = new ScrollSpy(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.scrollspy.Constructor = ScrollSpy

  $.fn.scrollspy.defaults = {
    offset: 10
  }


 /* SCROLLSPY NO CONFLICT
  * ===================== */

  $.fn.scrollspy.noConflict = function () {
    $.fn.scrollspy = old
    return this
  }


 /* SCROLLSPY DATA-API
  * ================== */

  $(window).on('load', function () {
    $('[data-spy="scroll"]').each(function () {
      var $spy = $(this)
      $spy.scrollspy($spy.data())
    })
  })

}(window.jQuery);
/* ========================================================
 * bootstrap-tab.js v2.3.1
 * http://twitter.github.com/bootstrap/javascript.html#tabs
 * ========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================== */


!function ($) {

  "use strict"; // jshint ;_;


 /* TAB CLASS DEFINITION
  * ==================== */

  var Tab = function (element) {
    this.element = $(element)
  }

  Tab.prototype = {

    constructor: Tab

  , show: function () {
      var $this = this.element
        , $ul = $this.closest('ul:not(.dropdown-menu)')
        , selector = $this.attr('data-target')
        , previous
        , $target
        , e

      if (!selector) {
        selector = $this.attr('href')
        selector = selector && selector.replace(/.*(?=#[^\s]*$)/, '') //strip for ie7
      }

      if ( $this.parent('li').hasClass('active') ) return

      previous = $ul.find('.active:last a')[0]

      e = $.Event('show', {
        relatedTarget: previous
      })

      $this.trigger(e)

      if (e.isDefaultPrevented()) return

      $target = $(selector)

      this.activate($this.parent('li'), $ul)
      this.activate($target, $target.parent(), function () {
        $this.trigger({
          type: 'shown'
        , relatedTarget: previous
        })
      })
    }

  , activate: function ( element, container, callback) {
      var $active = container.find('> .active')
        , transition = callback
            && $.support.transition
            && $active.hasClass('fade')

      function next() {
        $active
          .removeClass('active')
          .find('> .dropdown-menu > .active')
          .removeClass('active')

        element.addClass('active')

        if (transition) {
          element[0].offsetWidth // reflow for transition
          element.addClass('in')
        } else {
          element.removeClass('fade')
        }

        if ( element.parent('.dropdown-menu') ) {
          element.closest('li.dropdown').addClass('active')
        }

        callback && callback()
      }

      transition ?
        $active.one($.support.transition.end, next) :
        next()

      $active.removeClass('in')
    }
  }


 /* TAB PLUGIN DEFINITION
  * ===================== */

  var old = $.fn.tab

  $.fn.tab = function ( option ) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('tab')
      if (!data) $this.data('tab', (data = new Tab(this)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.tab.Constructor = Tab


 /* TAB NO CONFLICT
  * =============== */

  $.fn.tab.noConflict = function () {
    $.fn.tab = old
    return this
  }


 /* TAB DATA-API
  * ============ */

  $(document).on('click.tab.data-api', '[data-toggle="tab"], [data-toggle="pill"]', function (e) {
    e.preventDefault()
    $(this).tab('show')
  })

}(window.jQuery);
/* ===========================================================
 * bootstrap-tooltip.js v2.3.1
 * http://twitter.github.com/bootstrap/javascript.html#tooltips
 * Inspired by the original jQuery.tipsy by Jason Frame
 * ===========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */


!function ($) {

  "use strict"; // jshint ;_;


 /* TOOLTIP PUBLIC CLASS DEFINITION
  * =============================== */

  var Tooltip = function (element, options) {
    this.init('tooltip', element, options)
  }

  Tooltip.prototype = {

    constructor: Tooltip

  , init: function (type, element, options) {
      var eventIn
        , eventOut
        , triggers
        , trigger
        , i

      this.type = type
      this.$element = $(element)
      this.options = this.getOptions(options)
      this.enabled = true

      triggers = this.options.trigger.split(' ')

      for (i = triggers.length; i--;) {
        trigger = triggers[i]
        if (trigger == 'click') {
          this.$element.on('click.' + this.type, this.options.selector, $.proxy(this.toggle, this))
        } else if (trigger != 'manual') {
          eventIn = trigger == 'hover' ? 'mouseenter' : 'focus'
          eventOut = trigger == 'hover' ? 'mouseleave' : 'blur'
          this.$element.on(eventIn + '.' + this.type, this.options.selector, $.proxy(this.enter, this))
          this.$element.on(eventOut + '.' + this.type, this.options.selector, $.proxy(this.leave, this))
        }
      }

      this.options.selector ?
        (this._options = $.extend({}, this.options, { trigger: 'manual', selector: '' })) :
        this.fixTitle()
    }

  , getOptions: function (options) {
      options = $.extend({}, $.fn[this.type].defaults, this.$element.data(), options)

      if (options.delay && typeof options.delay == 'number') {
        options.delay = {
          show: options.delay
        , hide: options.delay
        }
      }

      return options
    }

  , enter: function (e) {
      var defaults = $.fn[this.type].defaults
        , options = {}
        , self

      this._options && $.each(this._options, function (key, value) {
        if (defaults[key] != value) options[key] = value
      }, this)

      self = $(e.currentTarget)[this.type](options).data(this.type)

      if (!self.options.delay || !self.options.delay.show) return self.show()

      clearTimeout(this.timeout)
      self.hoverState = 'in'
      this.timeout = setTimeout(function() {
        if (self.hoverState == 'in') self.show()
      }, self.options.delay.show)
    }

  , leave: function (e) {
      var self = $(e.currentTarget)[this.type](this._options).data(this.type)

      if (this.timeout) clearTimeout(this.timeout)
      if (!self.options.delay || !self.options.delay.hide) return self.hide()

      self.hoverState = 'out'
      this.timeout = setTimeout(function() {
        if (self.hoverState == 'out') self.hide()
      }, self.options.delay.hide)
    }

  , show: function () {
      var $tip
        , pos
        , actualWidth
        , actualHeight
        , placement
        , tp
        , e = $.Event('show')

      if (this.hasContent() && this.enabled) {
        this.$element.trigger(e)
        if (e.isDefaultPrevented()) return
        $tip = this.tip()
        this.setContent()

        if (this.options.animation) {
          $tip.addClass('fade')
        }

        placement = typeof this.options.placement == 'function' ?
          this.options.placement.call(this, $tip[0], this.$element[0]) :
          this.options.placement

        $tip
          .detach()
          .css({ top: 0, left: 0, display: 'block' })

        this.options.container ? $tip.appendTo(this.options.container) : $tip.insertAfter(this.$element)

        pos = this.getPosition()

        actualWidth = $tip[0].offsetWidth
        actualHeight = $tip[0].offsetHeight

        switch (placement) {
          case 'bottom':
            tp = {top: pos.top + pos.height, left: pos.left + pos.width / 2 - actualWidth / 2}
            break
          case 'top':
            tp = {top: pos.top - actualHeight, left: pos.left + pos.width / 2 - actualWidth / 2}
            break
          case 'left':
            tp = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left - actualWidth}
            break
          case 'right':
            tp = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left + pos.width}
            break
        }
        this.applyPlacement(tp, placement)
        this.$element.trigger('shown')
      }
    }

  , applyPlacement: function(offset, placement){

      var $tip = this.tip()
        , width = $tip[0].offsetWidth
        , height = $tip[0].offsetHeight
        , actualWidth
        , actualHeight
        , delta
        , replace

      $tip
        .offset(offset)
        .addClass(placement)
        .addClass('in')

      actualWidth = $tip[0].offsetWidth
      actualHeight = $tip[0].offsetHeight

      if (placement == 'top' && actualHeight != height) {
        offset.top = offset.top + height - actualHeight
        replace = true
      }

      if (placement == 'bottom' || placement == 'top') {
        delta = 0

        if (offset.left < 0){
          delta = offset.left * -2
          offset.left = 0
          $tip.offset(offset)
          actualWidth = $tip[0].offsetWidth
          actualHeight = $tip[0].offsetHeight
        }

        this.replaceArrow(delta - width + actualWidth, actualWidth, 'left')
      } else {
        this.replaceArrow(actualHeight - height, actualHeight, 'top')
      }
      if (replace) $tip.offset(offset)
    }

  , replaceArrow: function(delta, dimension, position){
      this
        .arrow()
        .css(position, delta ? (50 * (1 - delta / dimension) + "%") : '')
    }

  , setContent: function () {
      var $tip = this.tip()
        , title = this.getTitle()

      $tip.find('.tooltip-inner')[this.options.html ? 'html' : 'text'](title)
      $tip.removeClass('fade in top bottom left right')
    }

  , hide: function () {
      var that = this
        , $tip = this.tip()
        , e = $.Event('hide')

      this.$element.trigger(e)
      if (e.isDefaultPrevented()) return

      $tip.removeClass('in')

      function removeWithAnimation() {
        var timeout = setTimeout(function () {
          $tip.off($.support.transition.end).detach()
        }, 500)

        $tip.one($.support.transition.end, function () {
          clearTimeout(timeout)
          $tip.detach()
        })
      }

      $.support.transition && this.$tip.hasClass('fade') ?
        removeWithAnimation() :
        $tip.detach()

      this.$element.trigger('hidden')

      return this
    }

  , fixTitle: function () {
      var $e = this.$element
      if ($e.attr('title') || typeof($e.attr('data-original-title')) != 'string') {
        $e.attr('data-original-title', $e.attr('title') || '').attr('title', '')
      }
    }

  , hasContent: function () {
      return this.getTitle()
    }

  , getPosition: function () {
      var el = this.$element[0]
      return $.extend({}, (typeof el.getBoundingClientRect == 'function') ? el.getBoundingClientRect() : {
        width: el.offsetWidth
      , height: el.offsetHeight
      }, this.$element.offset())
    }

  , getTitle: function () {
      var title
        , $e = this.$element
        , o = this.options

      title = $e.attr('data-original-title')
        || (typeof o.title == 'function' ? o.title.call($e[0]) :  o.title)

      return title
    }

  , tip: function () {
      return this.$tip = this.$tip || $(this.options.template)
    }

  , arrow: function(){
      return this.$arrow = this.$arrow || this.tip().find(".tooltip-arrow")
    }

  , validate: function () {
      if (!this.$element[0].parentNode) {
        this.hide()
        this.$element = null
        this.options = null
      }
    }

  , enable: function () {
      this.enabled = true
    }

  , disable: function () {
      this.enabled = false
    }

  , toggleEnabled: function () {
      this.enabled = !this.enabled
    }

  , toggle: function (e) {
      var self = e ? $(e.currentTarget)[this.type](this._options).data(this.type) : this
      self.tip().hasClass('in') ? self.hide() : self.show()
    }

  , destroy: function () {
      this.hide().$element.off('.' + this.type).removeData(this.type)
    }

  }


 /* TOOLTIP PLUGIN DEFINITION
  * ========================= */

  var old = $.fn.tooltip

  $.fn.tooltip = function ( option ) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('tooltip')
        , options = typeof option == 'object' && option
      if (!data) $this.data('tooltip', (data = new Tooltip(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.tooltip.Constructor = Tooltip

  $.fn.tooltip.defaults = {
    animation: true
  , placement: 'top'
  , selector: false
  , template: '<div class="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
  , trigger: 'hover focus'
  , title: ''
  , delay: 0
  , html: false
  , container: false
  }


 /* TOOLTIP NO CONFLICT
  * =================== */

  $.fn.tooltip.noConflict = function () {
    $.fn.tooltip = old
    return this
  }

}(window.jQuery);

/* ===========================================================
 * bootstrap-popover.js v2.3.1
 * http://twitter.github.com/bootstrap/javascript.html#popovers
 * ===========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * =========================================================== */


!function ($) {

  "use strict"; // jshint ;_;


 /* POPOVER PUBLIC CLASS DEFINITION
  * =============================== */

  var Popover = function (element, options) {
    this.init('popover', element, options)
  }


  /* NOTE: POPOVER EXTENDS BOOTSTRAP-TOOLTIP.js
     ========================================== */

  Popover.prototype = $.extend({}, $.fn.tooltip.Constructor.prototype, {

    constructor: Popover

  , setContent: function () {
      var $tip = this.tip()
        , title = this.getTitle()
        , content = this.getContent()

      $tip.find('.popover-title')[this.options.html ? 'html' : 'text'](title)
      $tip.find('.popover-content')[this.options.html ? 'html' : 'text'](content)

      $tip.removeClass('fade top bottom left right in')
    }

  , hasContent: function () {
      return this.getTitle() || this.getContent()
    }

  , getContent: function () {
      var content
        , $e = this.$element
        , o = this.options

      content = (typeof o.content == 'function' ? o.content.call($e[0]) :  o.content)
        || $e.attr('data-content')

      return content
    }

  , tip: function () {
      if (!this.$tip) {
        this.$tip = $(this.options.template)
      }
      return this.$tip
    }

  , destroy: function () {
      this.hide().$element.off('.' + this.type).removeData(this.type)
    }

  })


 /* POPOVER PLUGIN DEFINITION
  * ======================= */

  var old = $.fn.popover

  $.fn.popover = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('popover')
        , options = typeof option == 'object' && option
      if (!data) $this.data('popover', (data = new Popover(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.popover.Constructor = Popover

  $.fn.popover.defaults = $.extend({} , $.fn.tooltip.defaults, {
    placement: 'right'
  , trigger: 'click'
  , content: ''
  , template: '<div class="popover"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
  })


 /* POPOVER NO CONFLICT
  * =================== */

  $.fn.popover.noConflict = function () {
    $.fn.popover = old
    return this
  }

}(window.jQuery);

/* ==========================================================
 * bootstrap-affix.js v2.3.1
 * http://twitter.github.com/bootstrap/javascript.html#affix
 * ==========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */


!function ($) {

 // AFFIX CLASS DEFINITION
  // ======================

  var Affix = function (element, options) {
    this.options = $.extend({}, Affix.DEFAULTS, options)
    this.$window = $(window)
      .on('scroll.bs.affix.data-api', $.proxy(this.checkPosition, this))
      .on('click.bs.affix.data-api',  $.proxy(this.checkPositionWithEventLoop, this))

    this.$element = $(element)
    this.affixed  =
    this.unpin    = null

    this.checkPosition()
  }

  Affix.RESET = 'affix affix-top affix-bottom'

  Affix.DEFAULTS = {
    offset: 0
  }

  Affix.prototype.checkPositionWithEventLoop = function () {
    setTimeout($.proxy(this.checkPosition, this), 1)
  }

  Affix.prototype.checkPosition = function () {
    if (!this.$element.is(':visible')) return

    var scrollHeight = $(document).height()
    var scrollTop    = this.$window.scrollTop()
    var position     = this.$element.offset()
    var offset       = this.options.offset
    var offsetTop    = offset.top
    var offsetBottom = offset.bottom

    if (typeof offset != 'object')         offsetBottom = offsetTop = offset
    if (typeof offsetTop == 'function')    offsetTop    = offset.top()
    if (typeof offsetBottom == 'function') offsetBottom = offset.bottom()

    var affix = this.unpin   != null && (scrollTop + this.unpin <= position.top) ? false :
                offsetBottom != null && (position.top + this.$element.height() >= scrollHeight - offsetBottom) ? 'bottom' :
                offsetTop    != null && (scrollTop <= offsetTop) ? 'top' : false

    if (this.affixed === affix) return
    if (this.unpin) this.$element.css('top', '')

    this.affixed = affix
    this.unpin   = affix == 'bottom' ? position.top - scrollTop : null

    this.$element.removeClass(Affix.RESET).addClass('affix' + (affix ? '-' + affix : ''))

    if (affix == 'bottom') {
      this.$element.offset({ top: document.body.offsetHeight - offsetBottom - this.$element.height() })
    }
  }


  // AFFIX PLUGIN DEFINITION
  // =======================

  var old = $.fn.affix

  $.fn.affix = function (option) {
    return this.each(function () {
      var $this   = $(this)
      var data    = $this.data('bs.affix')
      var options = typeof option == 'object' && option

      if (!data) $this.data('bs.affix', (data = new Affix(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.affix.Constructor = Affix


  // AFFIX NO CONFLICT
  // =================

  $.fn.affix.noConflict = function () {
    $.fn.affix = old
    return this
  }


  // AFFIX DATA-API
  // ==============

  $(window).on('load', function () {
    $('[data-spy="affix"]').each(function () {
      var $spy = $(this)
      var data = $spy.data()

      data.offset = data.offset || {}

      if (data.offsetBottom) data.offset.bottom = data.offsetBottom
      if (data.offsetTop)    data.offset.top    = data.offsetTop

      $spy.affix(data)
    })
  })


}(window.jQuery);
/* ==========================================================
 * bootstrap-alert.js v2.3.1
 * http://twitter.github.com/bootstrap/javascript.html#alerts
 * ==========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */


!function ($) {

  "use strict"; // jshint ;_;


 /* ALERT CLASS DEFINITION
  * ====================== */

  var dismiss = '[data-dismiss="alert"]'
    , Alert = function (el) {
        $(el).on('click', dismiss, this.close)
      }

  Alert.prototype.close = function (e) {
    var $this = $(this)
      , selector = $this.attr('data-target')
      , $parent

    if (!selector) {
      selector = $this.attr('href')
      selector = selector && selector.replace(/.*(?=#[^\s]*$)/, '') //strip for ie7
    }

    $parent = $(selector)

    e && e.preventDefault()

    $parent.length || ($parent = $this.hasClass('alert') ? $this : $this.parent())

    $parent.trigger(e = $.Event('close'))

    if (e.isDefaultPrevented()) return

    $parent.removeClass('in')

    function removeElement() {
      $parent
        .trigger('closed')
        .remove()
    }

    $.support.transition && $parent.hasClass('fade') ?
      $parent.on($.support.transition.end, removeElement) :
      removeElement()
  }


 /* ALERT PLUGIN DEFINITION
  * ======================= */

  var old = $.fn.alert

  $.fn.alert = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('alert')
      if (!data) $this.data('alert', (data = new Alert(this)))
      if (typeof option == 'string') data[option].call($this)
    })
  }

  $.fn.alert.Constructor = Alert


 /* ALERT NO CONFLICT
  * ================= */

  $.fn.alert.noConflict = function () {
    $.fn.alert = old
    return this
  }


 /* ALERT DATA-API
  * ============== */

  $(document).on('click.alert.data-api', dismiss, Alert.prototype.close)

}(window.jQuery);
/* ============================================================
 * bootstrap-button.js v2.3.1
 * http://twitter.github.com/bootstrap/javascript.html#buttons
 * ============================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */


!function ($) {

  "use strict"; // jshint ;_;


 /* BUTTON PUBLIC CLASS DEFINITION
  * ============================== */

  var Button = function (element, options) {
    this.$element = $(element)
    this.options = $.extend({}, $.fn.button.defaults, options)
  }

  Button.prototype.setState = function (state) {
    var d = 'disabled'
      , $el = this.$element
      , data = $el.data()
      , val = $el.is('input') ? 'val' : 'html'

    state = state + 'Text'
    data.resetText || $el.data('resetText', $el[val]())

    $el[val](data[state] || this.options[state])

    // push to event loop to allow forms to submit
    setTimeout(function () {
      state == 'loadingText' ?
        $el.addClass(d).attr(d, d) :
        $el.removeClass(d).removeAttr(d)
    }, 0)
  }

  Button.prototype.toggle = function () {
    var $parent = this.$element.closest('[data-toggle="buttons-radio"]')

    $parent && $parent
      .find('.active')
      .removeClass('active')

    this.$element.toggleClass('active')
  }


 /* BUTTON PLUGIN DEFINITION
  * ======================== */

  var old = $.fn.button

  $.fn.button = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('button')
        , options = typeof option == 'object' && option
      if (!data) $this.data('button', (data = new Button(this, options)))
      if (option == 'toggle') data.toggle()
      else if (option) data.setState(option)
    })
  }

  $.fn.button.defaults = {
    loadingText: 'loading...'
  }

  $.fn.button.Constructor = Button


 /* BUTTON NO CONFLICT
  * ================== */

  $.fn.button.noConflict = function () {
    $.fn.button = old
    return this
  }


 /* BUTTON DATA-API
  * =============== */

  $(document).on('click.button.data-api', '[data-toggle^=button]', function (e) {
    var $btn = $(e.target)
    if (!$btn.hasClass('btn')) $btn = $btn.closest('.btn')
    $btn.button('toggle')
  })

}(window.jQuery);
/* =============================================================
 * bootstrap-collapse.js v2.3.1
 * http://twitter.github.com/bootstrap/javascript.html#collapse
 * =============================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */


!function ($) {

  "use strict"; // jshint ;_;


 /* COLLAPSE PUBLIC CLASS DEFINITION
  * ================================ */

  var Collapse = function (element, options) {
    this.$element = $(element)
    this.options = $.extend({}, $.fn.collapse.defaults, options)

    if (this.options.parent) {
      this.$parent = $(this.options.parent)
    }

    this.options.toggle && this.toggle()
  }

  Collapse.prototype = {

    constructor: Collapse

  , dimension: function () {
      var hasWidth = this.$element.hasClass('width')
      return hasWidth ? 'width' : 'height'
    }

  , show: function () {
      var dimension
        , scroll
        , actives
        , hasData

      if (this.transitioning || this.$element.hasClass('in')) return

      dimension = this.dimension()
      scroll = $.camelCase(['scroll', dimension].join('-'))
      actives = this.$parent && this.$parent.find('> .accordion-group > .in')

      if (actives && actives.length) {
        hasData = actives.data('collapse')
        if (hasData && hasData.transitioning) return
        actives.collapse('hide')
        hasData || actives.data('collapse', null)
      }

      this.$element[dimension](0)
      this.transition('addClass', $.Event('show'), 'shown')
      $.support.transition && this.$element[dimension](this.$element[0][scroll])
    }

  , hide: function () {
      var dimension
      if (this.transitioning || !this.$element.hasClass('in')) return
      dimension = this.dimension()
      this.reset(this.$element[dimension]())
      this.transition('removeClass', $.Event('hide'), 'hidden')
      this.$element[dimension](0)
    }

  , reset: function (size) {
      var dimension = this.dimension()

      this.$element
        .removeClass('collapse')
        [dimension](size || 'auto')
        [0].offsetWidth

      this.$element[size !== null ? 'addClass' : 'removeClass']('collapse')

      return this
    }

  , transition: function (method, startEvent, completeEvent) {
      var that = this
        , complete = function () {
            if (startEvent.type == 'show') that.reset()
            that.transitioning = 0
            that.$element.trigger(completeEvent)
          }

      this.$element.trigger(startEvent)

      if (startEvent.isDefaultPrevented()) return

      this.transitioning = 1

      this.$element[method]('in')

      $.support.transition && this.$element.hasClass('collapse') ?
        this.$element.one($.support.transition.end, complete) :
        complete()
    }

  , toggle: function () {
      this[this.$element.hasClass('in') ? 'hide' : 'show']()
    }

  }


 /* COLLAPSE PLUGIN DEFINITION
  * ========================== */

  var old = $.fn.collapse

  $.fn.collapse = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('collapse')
        , options = $.extend({}, $.fn.collapse.defaults, $this.data(), typeof option == 'object' && option)
      if (!data) $this.data('collapse', (data = new Collapse(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.collapse.defaults = {
    toggle: true
  }

  $.fn.collapse.Constructor = Collapse


 /* COLLAPSE NO CONFLICT
  * ==================== */

  $.fn.collapse.noConflict = function () {
    $.fn.collapse = old
    return this
  }


 /* COLLAPSE DATA-API
  * ================= */

  $(document).on('click.collapse.data-api', '[data-toggle=collapse]', function (e) {
    var $this = $(this), href
      , target = $this.attr('data-target')
        || e.preventDefault()
        || (href = $this.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '') //strip for ie7
      , option = $(target).data('collapse') ? 'toggle' : $this.data()
    $this[$(target).hasClass('in') ? 'addClass' : 'removeClass']('collapsed')
    $(target).collapse(option)
  })

}(window.jQuery);
/* ==========================================================
 * bootstrap-carousel.js v2.3.1
 * http://twitter.github.com/bootstrap/javascript.html#carousel
 * ==========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */


!function ($) {

  "use strict"; // jshint ;_;


 /* CAROUSEL CLASS DEFINITION
  * ========================= */

  var Carousel = function (element, options) {
    this.$element = $(element)
    this.$indicators = this.$element.find('.carousel-indicators')
    this.options = options
    this.options.pause == 'hover' && this.$element
      .on('mouseenter', $.proxy(this.pause, this))
      .on('mouseleave', $.proxy(this.cycle, this))
  }

  Carousel.prototype = {

    cycle: function (e) {
      if (!e) this.paused = false
      if (this.interval) clearInterval(this.interval);
      this.options.interval
        && !this.paused
        && (this.interval = setInterval($.proxy(this.next, this), this.options.interval))
      return this
    }

  , getActiveIndex: function () {
      this.$active = this.$element.find('.item.active')
      this.$items = this.$active.parent().children()
      return this.$items.index(this.$active)
    }

  , to: function (pos) {
      var activeIndex = this.getActiveIndex()
        , that = this

      if (pos > (this.$items.length - 1) || pos < 0) return

      if (this.sliding) {
        return this.$element.one('slid', function () {
          that.to(pos)
        })
      }

      if (activeIndex == pos) {
        return this.pause().cycle()
      }

      return this.slide(pos > activeIndex ? 'next' : 'prev', $(this.$items[pos]))
    }

  , pause: function (e) {
      if (!e) this.paused = true
      if (this.$element.find('.next, .prev').length && $.support.transition.end) {
        this.$element.trigger($.support.transition.end)
        this.cycle(true)
      }
      clearInterval(this.interval)
      this.interval = null
      return this
    }

  , next: function () {
      if (this.sliding) return
      return this.slide('next')
    }

  , prev: function () {
      if (this.sliding) return
      return this.slide('prev')
    }

  , slide: function (type, next) {
      var $active = this.$element.find('.item.active')
        , $next = next || $active[type]()
        , isCycling = this.interval
        , direction = type == 'next' ? 'left' : 'right'
        , fallback  = type == 'next' ? 'first' : 'last'
        , that = this
        , e

      this.sliding = true

      isCycling && this.pause()

      $next = $next.length ? $next : this.$element.find('.item')[fallback]()

      e = $.Event('slide', {
        relatedTarget: $next[0]
      , direction: direction
      })

      if ($next.hasClass('active')) return

      if (this.$indicators.length) {
        this.$indicators.find('.active').removeClass('active')
        this.$element.one('slid', function () {
          var $nextIndicator = $(that.$indicators.children()[that.getActiveIndex()])
          $nextIndicator && $nextIndicator.addClass('active')
        })
      }

      if ($.support.transition && this.$element.hasClass('slide')) {
        this.$element.trigger(e)
        if (e.isDefaultPrevented()) return
        $next.addClass(type)
        $next[0].offsetWidth // force reflow
        $active.addClass(direction)
        $next.addClass(direction)
        this.$element.one($.support.transition.end, function () {
          $next.removeClass([type, direction].join(' ')).addClass('active')
          $active.removeClass(['active', direction].join(' '))
          that.sliding = false
          setTimeout(function () { that.$element.trigger('slid') }, 0)
        })
      } else {
        this.$element.trigger(e)
        if (e.isDefaultPrevented()) return
        $active.removeClass('active')
        $next.addClass('active')
        this.sliding = false
        this.$element.trigger('slid')
      }

      isCycling && this.cycle()

      return this
    }

  }


 /* CAROUSEL PLUGIN DEFINITION
  * ========================== */

  var old = $.fn.carousel

  $.fn.carousel = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('carousel')
        , options = $.extend({}, $.fn.carousel.defaults, typeof option == 'object' && option)
        , action = typeof option == 'string' ? option : options.slide
      if (!data) $this.data('carousel', (data = new Carousel(this, options)))
      if (typeof option == 'number') data.to(option)
      else if (action) data[action]()
      else if (options.interval) data.pause().cycle()
    })
  }

  $.fn.carousel.defaults = {
    interval: 5000
  , pause: 'hover'
  }

  $.fn.carousel.Constructor = Carousel


 /* CAROUSEL NO CONFLICT
  * ==================== */

  $.fn.carousel.noConflict = function () {
    $.fn.carousel = old
    return this
  }

 /* CAROUSEL DATA-API
  * ================= */

  $(document).on('click.carousel.data-api', '[data-slide], [data-slide-to]', function (e) {
    var $this = $(this), href
      , $target = $($this.attr('data-target') || (href = $this.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '')) //strip for ie7
      , options = $.extend({}, $target.data(), $this.data())
      , slideIndex

    $target.carousel(options)

    if (slideIndex = $this.attr('data-slide-to')) {
      $target.data('carousel').pause().to(slideIndex).cycle()
    }

    e.preventDefault()
  })

}(window.jQuery);
/* =============================================================
 * bootstrap-typeahead.js v2.3.1
 * http://twitter.github.com/bootstrap/javascript.html#typeahead
 * =============================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */


!function($){

  "use strict"; // jshint ;_;


 /* TYPEAHEAD PUBLIC CLASS DEFINITION
  * ================================= */

  var Typeahead = function (element, options) {
    this.$element = $(element)
    this.options = $.extend({}, $.fn.typeahead.defaults, options)
    this.matcher = this.options.matcher || this.matcher
    this.sorter = this.options.sorter || this.sorter
    this.highlighter = this.options.highlighter || this.highlighter
    this.updater = this.options.updater || this.updater
    this.source = this.options.source
    this.$menu = $(this.options.menu)
    this.shown = false
    this.listen()
  }

  Typeahead.prototype = {

    constructor: Typeahead

  , select: function () {
      var val = this.$menu.find('.active').attr('data-value')
      this.$element
        .val(this.updater(val))
        .change()
      return this.hide()
    }

  , updater: function (item) {
      return item
    }

  , show: function () {
      var pos = $.extend({}, this.$element.position(), {
        height: this.$element[0].offsetHeight
      })

      this.$menu
        .insertAfter(this.$element)
        .css({
          top: pos.top + pos.height
        , left: pos.left
        })
        .show()

      this.shown = true
      return this
    }

  , hide: function () {
      this.$menu.hide()
      this.shown = false
      return this
    }

  , lookup: function (event) {
      var items

      this.query = this.$element.val()

      if (!this.query || this.query.length < this.options.minLength) {
        return this.shown ? this.hide() : this
      }

      items = $.isFunction(this.source) ? this.source(this.query, $.proxy(this.process, this)) : this.source

      return items ? this.process(items) : this
    }

  , process: function (items) {
      var that = this

      items = $.grep(items, function (item) {
        return that.matcher(item)
      })

      items = this.sorter(items)

      if (!items.length) {
        return this.shown ? this.hide() : this
      }

      return this.render(items.slice(0, this.options.items)).show()
    }

  , matcher: function (item) {
      return ~item.toLowerCase().indexOf(this.query.toLowerCase())
    }

  , sorter: function (items) {
      var beginswith = []
        , caseSensitive = []
        , caseInsensitive = []
        , item

      while (item = items.shift()) {
        if (!item.toLowerCase().indexOf(this.query.toLowerCase())) beginswith.push(item)
        else if (~item.indexOf(this.query)) caseSensitive.push(item)
        else caseInsensitive.push(item)
      }

      return beginswith.concat(caseSensitive, caseInsensitive)
    }

  , highlighter: function (item) {
      var query = this.query.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&')
      return item.replace(new RegExp('(' + query + ')', 'ig'), function ($1, match) {
        return '<strong>' + match + '</strong>'
      })
    }

  , render: function (items) {
      var that = this

      items = $(items).map(function (i, item) {
        i = $(that.options.item).attr('data-value', item)
        i.find('a').html(that.highlighter(item))
        return i[0]
      })

      items.first().addClass('active')
      this.$menu.html(items)
      return this
    }

  , next: function (event) {
      var active = this.$menu.find('.active').removeClass('active')
        , next = active.next()

      if (!next.length) {
        next = $(this.$menu.find('li')[0])
      }

      next.addClass('active')
    }

  , prev: function (event) {
      var active = this.$menu.find('.active').removeClass('active')
        , prev = active.prev()

      if (!prev.length) {
        prev = this.$menu.find('li').last()
      }

      prev.addClass('active')
    }

  , listen: function () {
      this.$element
        .on('focus',    $.proxy(this.focus, this))
        .on('blur',     $.proxy(this.blur, this))
        .on('keypress', $.proxy(this.keypress, this))
        .on('keyup',    $.proxy(this.keyup, this))

      if (this.eventSupported('keydown')) {
        this.$element.on('keydown', $.proxy(this.keydown, this))
      }

      this.$menu
        .on('click', $.proxy(this.click, this))
        .on('mouseenter', 'li', $.proxy(this.mouseenter, this))
        .on('mouseleave', 'li', $.proxy(this.mouseleave, this))
    }

  , eventSupported: function(eventName) {
      var isSupported = eventName in this.$element
      if (!isSupported) {
        this.$element.setAttribute(eventName, 'return;')
        isSupported = typeof this.$element[eventName] === 'function'
      }
      return isSupported
    }

  , move: function (e) {
      if (!this.shown) return

      switch(e.keyCode) {
        case 9: // tab
        case 13: // enter
        case 27: // escape
          e.preventDefault()
          break

        case 38: // up arrow
          e.preventDefault()
          this.prev()
          break

        case 40: // down arrow
          e.preventDefault()
          this.next()
          break
      }

      e.stopPropagation()
    }

  , keydown: function (e) {
      this.suppressKeyPressRepeat = ~$.inArray(e.keyCode, [40,38,9,13,27])
      this.move(e)
    }

  , keypress: function (e) {
      if (this.suppressKeyPressRepeat) return
      this.move(e)
    }

  , keyup: function (e) {
      switch(e.keyCode) {
        case 40: // down arrow
        case 38: // up arrow
        case 16: // shift
        case 17: // ctrl
        case 18: // alt
          break

        case 9: // tab
        case 13: // enter
          if (!this.shown) return
          this.select()
          break

        case 27: // escape
          if (!this.shown) return
          this.hide()
          break

        default:
          this.lookup()
      }

      e.stopPropagation()
      e.preventDefault()
  }

  , focus: function (e) {
      this.focused = true
    }

  , blur: function (e) {
      this.focused = false
      if (!this.mousedover && this.shown) this.hide()
    }

  , click: function (e) {
      e.stopPropagation()
      e.preventDefault()
      this.select()
      this.$element.focus()
    }

  , mouseenter: function (e) {
      this.mousedover = true
      this.$menu.find('.active').removeClass('active')
      $(e.currentTarget).addClass('active')
    }

  , mouseleave: function (e) {
      this.mousedover = false
      if (!this.focused && this.shown) this.hide()
    }

  }


  /* TYPEAHEAD PLUGIN DEFINITION
   * =========================== */

  var old = $.fn.typeahead

  $.fn.typeahead = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('typeahead')
        , options = typeof option == 'object' && option
      if (!data) $this.data('typeahead', (data = new Typeahead(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.typeahead.defaults = {
    source: []
  , items: 8
  , menu: '<ul class="typeahead dropdown-menu"></ul>'
  , item: '<li><a href="#"></a></li>'
  , minLength: 1
  }

  $.fn.typeahead.Constructor = Typeahead


 /* TYPEAHEAD NO CONFLICT
  * =================== */

  $.fn.typeahead.noConflict = function () {
    $.fn.typeahead = old
    return this
  }


 /* TYPEAHEAD DATA-API
  * ================== */

  $(document).on('focus.typeahead.data-api', '[data-provide="typeahead"]', function (e) {
    var $this = $(this)
    if ($this.data('typeahead')) return
    $this.typeahead($this.data())
  })

}(window.jQuery);
// $.fn.colorPicker
/* 选色器 */
(function(window, $) {
	var lang = {
		TITLE: "请选择颜色",
		RESET: "重置颜色"
	}
	$.fn.colorPicker = function(conf) {
		// Config for plug
		var defaultData = ["#FFFFFF", "#E26F50", "#EE8C0C", "#FDE163", "#91CE31", "#3497DB", "#82939E", "#B2C0D1"];
		var config = $.extend({
			id: 'jquery-colour-picker', // id of colour-picker container
			horizontal: false, // 是否垂直排列色板
			// title: lang.TITLE, // Default dialogue title
			speed: 200, // Speed of dialogue-animation
			position: { my: "left top", at: "left top" },
			data: defaultData,
			style: "",
			reset: true,
			onPick: null // 选择时触发的回调
		}, conf);

		// Add the colour-picker dialogue if not added
		var colourPicker = $('#' + config.id);

		if (!colourPicker.length) {
			colourPicker = $('<div id="' + config.id + '" class="jquery-colour-picker ' + config.style + '"></div>').appendTo(document.body).hide();

			// Remove the colour-picker if you click outside it (on body)
			$(document.body).click(function(event) {
				if (!($(event.target).is('#' + config.id) || $(event.target).parents('#' + config.id).length)) {
					colourPicker.slideUp(config.speed);
				}
			});
		}

		if (config.horizontal) {
			colourPicker.addClass("horizontal");
			//垂直模式时，去掉title
			config.title = "";
		}

		// For every select passed to the plug-in
		return this.each(function() {
			if($.data(this, 'colorPicker')){
				return false;
			}
			// input element
			var loc = '',
				hex, title,
				createItemTemp = function(hex, title) {
					return '<li><a href="#" title="' + title + '" rel="' + hex + '" style="background: ' + hex + ';">' + title + '</a></li>'
				};

			// 当由data属性提供数据
			if (config.data) {
				for (var i = 0, len = config.data.length; i < len; i++) {
					//当data项是颜色值
					if (typeof config.data[i] === 'string') {
						loc += createItemTemp(config.data[i], config.data[i]);
						//当data项是键值对
					} else {
						loc += createItemTemp(config.data[i].hex, config.data[i].title);
					}
				}
				// 创建清除按钮
				if(config.reset){
					loc += createItemTemp("", lang.RESET);
				}

				// 为select元素时，从option中获取数据
			} else {
				//@Debug:
				throw new Error('数据不存在')
			}

			// When you click the ctrl
			var ctrl = config.ctrl && config.ctrl.length ? config.ctrl : $(this);
			ctrl.click(function() {
				// Show the colour-picker next to the ctrl and fill it with the colours in the select that used to be there
				var heading = config.title ? '<h2>' + config.title + '</h2>' : '';
				var pos = $.extend({ of: ctrl }, config.position);
				colourPicker.html(heading + '<ul>' + loc + '</ul>').css({
					position: 'absolute'
				})
				.slideDown(config.speed)
				.position(pos);

				return false;
			});

			// When you click a colour in the colour-picker
			colourPicker.on('click', 'a', function() {
				// The hex is stored in the link's rel-attribute
				var hex = $(this).attr('rel'),
					title = $(this).text();
				config.onPick && config.onPick(hex, title)
				// Hide the colour-picker and return false
				colourPicker.slideUp(config.speed);
				return false;
			});

			$.data(this, "colorPicker", true);
		});
	}

})(window, window.jQuery);


/**
 * @license
 * =========================================================
 * bootstrap-datetimepicker.js
 * http://www.eyecon.ro/bootstrap-datepicker
 * =========================================================
 * Copyright 2012 Stefan Petre
 *
 * Contributions:
 *  - Andrew Rowls
 *  - Thiago de Arruda
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * =========================================================
 */

(function($) {

  // Picker object
  var smartPhone = (window.orientation != undefined);
  var DateTimePicker = function(element, options) {
    this.id = dpgId++;
    this.init(element, options);
  };

  var dateToDate = function(dt) {
    if (typeof dt === 'string') {
      return new Date(dt);
    }
    return dt;
  };

  DateTimePicker.prototype = {
    constructor: DateTimePicker,

    init: function(element, options) {
      var icon;
      if (!(options.pickTime || options.pickDate))
        throw new Error('Must choose at least one picker');
      this.options = options;
      this.$element = $(element);
      this.language = options.language in dates ? options.language : 'en'
      this.pickDate = options.pickDate;
      this.pickTime = options.pickTime;
      this.range = options.range;
      // 当日期范围可用时，禁用时分秒选择
      if(this.range) this.pickTime = false;
      this.isInput = this.$element.is('input');
      this.component = false;
      if (this.$element.is('.input-append') || this.$element.is('.input-prepend')) {
    	  this.component = this.$element.find('.add-on');
      } else {
        this.component = this.options.component
      }

      this.format = options.format;
      if (!this.format) {
        if (this.isInput) this.format = this.$element.data('format');
        else this.format = this.$element.find('input').data('format');
        if (!this.format) this.format = 'mm/dd/yyyy';
      }
      this._compileFormat();
      if (this.component) {
        icon = this.component.find('i');
      }
      if (this.pickTime) {
        if (icon && icon.length) {
        	this.timeIcon = icon.data('time-icon');
        	icon.addClass(this.timeIcon);
        }
        if (!this.timeIcon) this.timeIcon = 'glyphicon-time';
      }
      if (this.pickDate) {
        if (icon && icon.length) {
        	this.dateIcon = icon.data('date-icon');
        	icon.removeClass(this.timeIcon);
        	icon.addClass(this.dateIcon);
        }
        if (!this.dateIcon) this.dateIcon = 'glyphicon-calendar';
      }
      this.widget = $(getTemplate(this.timeIcon, options.pickDate, options.pickTime, options.pick12HourFormat, options.pickSeconds, options.collapse)).appendTo('body');
      this.widget.find('.datepicker-months thead').append('<tr><th colspan="7">' + dates[this.language].selectMonth + '</th></tr>');
      this.widget.find('.datepicker-years thead').append('<tr><th colspan="7">' + dates[this.language].selectYear + '</th></tr>');
      this.minViewMode = options.minViewMode||this.$element.data('date-minviewmode')||0;
      if (typeof this.minViewMode === 'string') {
        switch (this.minViewMode) {
          case 'months':
            this.minViewMode = 1;
          break;
          case 'years':
            this.minViewMode = 2;
          break;
          default:
            this.minViewMode = 0;
          break;
        }
      }
      this.viewMode = options.viewMode||this.$element.data('date-viewmode')||0;
      if (typeof this.viewMode === 'string') {
        switch (this.viewMode) {
          case 'months':
            this.viewMode = 1;
          break;
          case 'years':
            this.viewMode = 2;
          break;
          default:
            this.viewMode = 0;
          break;
        }
      }
      this.startViewMode = this.viewMode;
      this.weekStart = options.weekStart||this.$element.data('date-weekstart')||0;
      this.weekEnd = this.weekStart === 0 ? 6 : this.weekStart - 1;
      this.setStartDate(options.startDate || this.$element.data('date-startdate'));
      this.setEndDate(options.endDate || this.$element.data('date-enddate'));
      this.fillDow();
      this.fillMonths();
      this.fillHours();
      this.fillMinutes();
      this.fillSeconds();
      this.update();
      this.showMode();
      this._attachDatePickerEvents();
    },

    show: function(e) {
      this.widget.show();
      this.height = this.component ? this.component.outerHeight() : this.$element.outerHeight();
      this.place();
      this.$element.trigger({
        type: 'show',
        date: this._date,
        lastDate: this._lastDate
      });
      this._attachDatePickerGlobalEvents();
      var expanded = this.widget.find(".collapse.in");
      this.widget.find(".accordion-toggle").html(this.formatDate(this._date).split(" ")[+!expanded.find(".timepicker").length])


      if (e) {
        e.stopPropagation();
        e.preventDefault();
      }
    },

    disable: function(){
          this.$element.find('input').prop('disabled',true);
          this._detachDatePickerEvents();
    },
    enable: function(){
          this.$element.find('input').prop('disabled',false);
          this._attachDatePickerEvents();
    },

    hide: function() {
      // Ignore event if in the middle of a picker transition
      var collapse = this.widget.find('.collapse')
      for (var i = 0; i < collapse.length; i++) {
        var collapseData = collapse.eq(i).data('collapse');
        if (collapseData && collapseData.transitioning)
          return;
      }
      this.widget.hide();
      this.viewMode = this.startViewMode;
      this.showMode();
      this.set();
      this.$element.trigger({
        type: 'hide',
        date: this._date,
        lastDate: this._lastDate
      });
      this._detachDatePickerGlobalEvents();
    },

    // set: function() {
    //   var formatted = '';
    //   if (!this._unset) formatted = this.formatDate(this._date);
    //   if (!this.isInput) {
    //     if (this.component){
    //       var input = this.$element.find('input');
    //       input.val(formatted);
    //       this._resetMaskPos(input);
    //     }
    //     this.$element.data('date', formatted);
    //   } else {
    //     this.$element.val(formatted);
    //     this._resetMaskPos(this.$element);
    //   }
    // },
    set: function() {
      var formatted = '';
      if (!this._unset) {
        if(this.range && this._lastDate) {
          var start, end;
          if(this._date.valueOf() > this._lastDate.valueOf()){
            start = this.formatDate(this._lastDate);
            end = this.formatDate(this._date);
          } else {
            start = this.formatDate(this._date);
            end = this.formatDate(this._lastDate);
          }
          formatted = start + " - " + end;
        }
        else {
          formatted = this.formatDate(this._date);
        }
      }
      if (!this.isInput) {
        if (this.component){
          var input = this.$element.find('input');
          input.val(formatted);
          this._resetMaskPos(input);
        }
        this.$element.data('date', formatted);
      } else {
        this.$element.val(formatted);
        this._resetMaskPos(this.$element);
      }
    },

    setValue: function(newDate) {
      if (!newDate) {
        this._unset = true;
      } else {
        this._unset = false;
      }
      if (typeof newDate === 'string') {
        if(this.range){
          this._lastDate = this.parseDate(newDate.split(" - ")[0])
          this._date = this.parseDate(newDate.split(" - ")[1]);
        }
      } else if(newDate) {
        this._date = new Date(newDate);
      }
      this.set();
      this.viewDate = UTCDate(this._date.getUTCFullYear(), this._date.getUTCMonth(), 1, 0, 0, 0, 0);
      this.fillDate();
      this.fillTime();
    },

    getDate: function() {
      if (this._unset) return null;
      return new Date(this._date.valueOf());
    },

    getLastDate: function(){
      if (this._unset) return null;
      return this._lastDate ? new Date(this._lastDate.valueOf()) : null;
    },

    setDate: function(date) {
      if (!date) this.setValue(null);
      else this.setValue(date.valueOf());
    },

    setStartDate: function(date) {
      if (date instanceof Date) {
        this.startDate = date;
      } else if (typeof date === 'string') {
        this.startDate = new UTCDate(date);
        if (! this.startDate.getUTCFullYear()) {
          this.startDate = -Infinity;
        }
      } else {
        this.startDate = -Infinity;
      }
      if (this.viewDate) {
        this.update();
      }
    },

    setEndDate: function(date) {
      if (date instanceof Date) {
        this.endDate = date;
      } else if (typeof date === 'string') {
        this.endDate = new UTCDate(date);
        if (! this.endDate.getUTCFullYear()) {
          this.endDate = Infinity;
        }
      } else {
        this.endDate = Infinity;
      }
      if (this.viewDate) {
        this.update();
      }
    },

    getLocalDate: function() {
      if (this._unset) return null;
      var d = this._date;
      return new Date(d.getUTCFullYear(), d.getUTCMonth(), d.getUTCDate(),
                      d.getUTCHours(), d.getUTCMinutes(), d.getUTCSeconds(), d.getUTCMilliseconds());
    },

    getLocalLastDate: function() {
      if (this._unset) return null;
      var d = this._lastDate;
      return d ? new Date(d.getUTCFullYear(), d.getUTCMonth(), d.getUTCDate(),
                      d.getUTCHours(), d.getUTCMinutes(), d.getUTCSeconds(), d.getUTCMilliseconds()) :
                null
    },

    setLocalDate: function(localDate) {
      if (!localDate) this.setValue(null);
      else
        this.setValue(Date.UTC(
          localDate.getFullYear(),
          localDate.getMonth(),
          localDate.getDate(),
          localDate.getHours(),
          localDate.getMinutes(),
          localDate.getSeconds(),
          localDate.getMilliseconds()));
    },

    place: function(){
      var position = 'absolute';
      var offset = this.component ? this.component.offset() : this.$element.offset();
      this.width = this.component ? this.component.outerWidth() : this.$element.outerWidth();
      offset.top = offset.top + this.height;

      var $window = $(window);
      
      if ( this.options.width != undefined ) {
        this.widget.width( this.options.width );
      }
      
      if ( this.options.orientation == 'left' ) {
        this.widget.addClass( 'left-oriented' );
        offset.left   = offset.left - this.widget.width() + 20;
      }
      
      if (this._isInFixed()) {
        position = 'fixed';
        offset.top -= $window.scrollTop();
        offset.left -= $window.scrollLeft();
      }

      if ($window.width() < offset.left + this.widget.outerWidth()) {
        offset.right = $window.width() - offset.left - this.width;
        offset.left = 'auto';
        this.widget.addClass('pull-right');
      } else {
        offset.right = 'auto';
        this.widget.removeClass('pull-right');
      }

      this.widget.css({
        position: position,
        top: offset.top,
        left: offset.left,
        right: offset.right
      });
    },

    notifyChange: function(){
      this.$element.trigger({
        type: 'changeDate',
        date: this.getDate(),
        lastDate: this.getLastDate(),
        localDate: this.getLocalDate(),
        localLastDate: this.getLocalLastDate()
      });
    },

    update: function(newDate){
      var dateStr = newDate;
      if (!dateStr) {
        if (this.isInput) {
          dateStr = this.$element.val();
        } else {
          dateStr = this.$element.find('input').val();
        }
        if (dateStr) {
          if(this.range){
            var dateStrArr = dateStr.split(" - ");
            this._lastDate = this.parseDate(dateStrArr[0]);
            this._date = this.parseDate(dateStrArr[1]);
          } else {
            this._date = this.parseDate(dateStr);
          }
        }
        if (!this._date) {
          var tmp = new Date()
          this._date = UTCDate(tmp.getFullYear(),
                              tmp.getMonth(),
                              tmp.getDate(),
                              tmp.getHours(),
                              tmp.getMinutes(),
                              tmp.getSeconds(),
                              tmp.getMilliseconds())
        }
      }
      this.viewDate = UTCDate(this._date.getUTCFullYear(), this._date.getUTCMonth(), 1, 0, 0, 0, 0);
      this.fillDate();
      this.fillTime();
    },

    fillDow: function() {
      var dowCnt = this.weekStart;
      var html = $('<tr>');
      while (dowCnt < this.weekStart + 7) {
        html.append('<th class="dow">' + dates[this.language].daysMin[(dowCnt++) % 7] + '</th>');
      }
      this.widget.find('.datepicker-days thead').append(html);
    },

    fillMonths: function() {
      var html = '';
      var i = 0
      while (i < 12) {
        html += '<span class="month">' + dates[this.language].monthsShort[i++] + '</span>';
      }
      this.widget.find('.datepicker-months td').append(html);
    },

    // fillDate: function() {
    //   var year = this.viewDate.getUTCFullYear();
    //   var month = this.viewDate.getUTCMonth();
    //   var currentDate = UTCDate(
    //     this._date.getUTCFullYear(),
    //     this._date.getUTCMonth(),
    //     this._date.getUTCDate(),
    //     0, 0, 0, 0
    //   );
    //   var startYear  = typeof this.startDate === 'object' ? this.startDate.getUTCFullYear() : -Infinity;
    //   var startMonth = typeof this.startDate === 'object' ? this.startDate.getUTCMonth() : -1;
    //   var endYear  = typeof this.endDate === 'object' ? this.endDate.getUTCFullYear() : Infinity;
    //   var endMonth = typeof this.endDate === 'object' ? this.endDate.getUTCMonth() : 12;

    //   this.widget.find('.datepicker-days').find('.disabled').removeClass('disabled');
    //   this.widget.find('.datepicker-months').find('.disabled').removeClass('disabled');
    //   this.widget.find('.datepicker-years').find('.disabled').removeClass('disabled');

    //   this.widget.find('.datepicker-days th:eq(1)').text(
    //     dates[this.language].months[month] + ' ' + year);

    //   var prevMonth = UTCDate(year, month-1, 28, 0, 0, 0, 0);
    //   var day = DPGlobal.getDaysInMonth(
    //     prevMonth.getUTCFullYear(), prevMonth.getUTCMonth());
    //   prevMonth.setUTCDate(day);
    //   prevMonth.setUTCDate(day - (prevMonth.getUTCDay() - this.weekStart + 7) % 7);
    //   if ((year == startYear && month <= startMonth) || year < startYear) {
    //     this.widget.find('.datepicker-days th:eq(0)').addClass('disabled');
    //   }
    //   if ((year == endYear && month >= endMonth) || year > endYear) {
    //     this.widget.find('.datepicker-days th:eq(2)').addClass('disabled');
    //   }

    //   var nextMonth = new Date(prevMonth.valueOf());
    //   nextMonth.setUTCDate(nextMonth.getUTCDate() + 42);
    //   nextMonth = nextMonth.valueOf();
    //   var html = [];
    //   var row;
    //   var clsName;
    //   while (prevMonth.valueOf() < nextMonth) {
    //     if (prevMonth.getUTCDay() === this.weekStart) {
    //       row = $('<tr>');
    //       html.push(row);
    //     }
    //     clsName = '';
    //     if (prevMonth.getUTCFullYear() < year ||
    //         (prevMonth.getUTCFullYear() == year &&
    //          prevMonth.getUTCMonth() < month)) {
    //       clsName += ' old';
    //     } else if (prevMonth.getUTCFullYear() > year ||
    //                (prevMonth.getUTCFullYear() == year &&
    //                 prevMonth.getUTCMonth() > month)) {
    //       clsName += ' new';
    //     }
    //     if (prevMonth.valueOf() === currentDate.valueOf()) {
    //       clsName += ' active';
    //     }
    //     if ((prevMonth.valueOf() + 86400000) <= this.startDate) {
    //       clsName += ' disabled';
    //     }
    //     if (prevMonth.valueOf() > this.endDate) {
    //       clsName += ' disabled';
    //     }
    //     row.append('<td class="day' + clsName + '">' + prevMonth.getUTCDate() + '</td>');
    //     prevMonth.setUTCDate(prevMonth.getUTCDate() + 1);
    //   }
    //   this.widget.find('.datepicker-days tbody').empty().append(html);
    //   var currentYear = this._date.getUTCFullYear();

    //   var months = this.widget.find('.datepicker-months').find(
    //     'th:eq(1)').text(year).end().find('span').removeClass('active');
    //   if (currentYear === year) {
    //     months.eq(this._date.getUTCMonth()).addClass('active');
    //   }
    //   if (currentYear - 1 < startYear) {
    //     this.widget.find('.datepicker-months th:eq(0)').addClass('disabled');
    //   }
    //   if (currentYear + 1 > endYear) {
    //     this.widget.find('.datepicker-months th:eq(2)').addClass('disabled');
    //   }
    //   for (var i = 0; i < 12; i++) {
    //     if ((year == startYear && startMonth > i) || (year < startYear)) {
    //       $(months[i]).addClass('disabled');
    //     } else if ((year == endYear && endMonth < i) || (year > endYear)) {
    //       $(months[i]).addClass('disabled');
    //     }
    //   }

    //   html = '';
    //   year = parseInt(year/10, 10) * 10;
    //   var yearCont = this.widget.find('.datepicker-years').find(
    //     'th:eq(1)').text(year + '-' + (year + 9)).end().find('td');
    //   this.widget.find('.datepicker-years').find('th').removeClass('disabled');
    //   if (startYear > year) {
    //     this.widget.find('.datepicker-years').find('th:eq(0)').addClass('disabled');
    //   }
    //   if (endYear < year+9) {
    //     this.widget.find('.datepicker-years').find('th:eq(2)').addClass('disabled');
    //   }
    //   year -= 1;
    //   for (var i = -1; i < 11; i++) {
    //     html += '<span class="year' + (i === -1 || i === 10 ? ' old' : '') + (currentYear === year ? ' active' : '') + ((year < startYear || year > endYear) ? ' disabled' : '') + '">' + year + '</span>';
    //     year += 1;
    //   }
    //   yearCont.html(html);
    // },

    fillDate: function() {
      var year = this.viewDate.getUTCFullYear();
      var month = this.viewDate.getUTCMonth();
      var currentDate = UTCDate(
        this._date.getUTCFullYear(),
        this._date.getUTCMonth(),
        this._date.getUTCDate(),
        0, 0, 0, 0
      );
      var startYear  = typeof this.startDate === 'object' ? this.startDate.getUTCFullYear() : -Infinity;
      var startMonth = typeof this.startDate === 'object' ? this.startDate.getUTCMonth() : -1;
      var endYear  = typeof this.endDate === 'object' ? this.endDate.getUTCFullYear() : Infinity;
      var endMonth = typeof this.endDate === 'object' ? this.endDate.getUTCMonth() : 12;

      this.widget.find('.datepicker-days').find('.disabled').removeClass('disabled');
      this.widget.find('.datepicker-months').find('.disabled').removeClass('disabled');
      this.widget.find('.datepicker-years').find('.disabled').removeClass('disabled');

      this.widget.find('.datepicker-days th:eq(1)').text(
        dates[this.language].months[month] + ' ' + year);

      var prevMonth = UTCDate(year, month-1, 28, 0, 0, 0, 0);
      var day = DPGlobal.getDaysInMonth(
        prevMonth.getUTCFullYear(), prevMonth.getUTCMonth());
      prevMonth.setUTCDate(day);
      prevMonth.setUTCDate(day - (prevMonth.getUTCDay() - this.weekStart + 7) % 7);
      if ((year == startYear && month <= startMonth) || year < startYear) {
        this.widget.find('.datepicker-days th:eq(0)').addClass('disabled');
      }
      if ((year == endYear && month >= endMonth) || year > endYear) {
        this.widget.find('.datepicker-days th:eq(2)').addClass('disabled');
      }

      var nextMonth = new Date(prevMonth.valueOf());
      nextMonth.setUTCDate(nextMonth.getUTCDate() + 42);
      nextMonth = nextMonth.valueOf();
      var html = [];
      var row;
      var clsName;
      while (prevMonth.valueOf() < nextMonth) {
        if (prevMonth.getUTCDay() === this.weekStart) {
          row = $('<tr>');
          html.push(row);
        }
        clsName = '';
        if (prevMonth.getUTCFullYear() < year ||
            (prevMonth.getUTCFullYear() == year &&
             prevMonth.getUTCMonth() < month)) {
          clsName += ' old';
        } else if (prevMonth.getUTCFullYear() > year ||
                   (prevMonth.getUTCFullYear() == year &&
                    prevMonth.getUTCMonth() > month)) {
          clsName += ' new';
        }
        if(prevMonth.getUTCDay() === 0 || prevMonth.getUTCDay() == 6){
          clsName += ' weekend';
        }

        if(!this._lastDate) {
          if (prevMonth.valueOf() === currentDate.valueOf()) {
            clsName += ' active';
          }
        } else {
          var minDate = Math.min(this._lastDate.valueOf(), currentDate.valueOf()),
              maxDate = Math.max(this._lastDate.valueOf(), currentDate.valueOf())
          if(prevMonth.valueOf() >= minDate && prevMonth.valueOf() <= maxDate){
            clsName += ' active';
          }
        }

        if ((prevMonth.valueOf() + 86400000) <= this.startDate) {
          clsName += ' disabled';
        }
        if (prevMonth.valueOf() > this.endDate) {
          clsName += ' disabled';
        }
        row.append('<td class="day' + clsName + '">' + prevMonth.getUTCDate() + '</td>');
        prevMonth.setUTCDate(prevMonth.getUTCDate() + 1);
      }
      this.widget.find('.datepicker-days tbody').empty().append(html);
      var currentYear = this._date.getUTCFullYear();

      var months = this.widget.find('.datepicker-months').find(
        'th:eq(1)').text(year).end().find('span').removeClass('active');
      if (currentYear === year) {
        months.eq(this._date.getUTCMonth()).addClass('active');
      }
      if (currentYear - 1 < startYear) {
        this.widget.find('.datepicker-months th:eq(0)').addClass('disabled');
      }
      if (currentYear + 1 > endYear) {
        this.widget.find('.datepicker-months th:eq(2)').addClass('disabled');
      }
      for (var i = 0; i < 12; i++) {
        if ((year == startYear && startMonth > i) || (year < startYear)) {
          $(months[i]).addClass('disabled');
        } else if ((year == endYear && endMonth < i) || (year > endYear)) {
          $(months[i]).addClass('disabled');
        }
      }

      html = '';
      year = parseInt(year/10, 10) * 10;
      var yearCont = this.widget.find('.datepicker-years').find(
        'th:eq(1)').text(year + '-' + (year + 9)).end().find('td');
      this.widget.find('.datepicker-years').find('th').removeClass('disabled');
      if (startYear > year) {
        this.widget.find('.datepicker-years').find('th:eq(0)').addClass('disabled');
      }
      if (endYear < year+9) {
        this.widget.find('.datepicker-years').find('th:eq(2)').addClass('disabled');
      }
      year -= 1;
      for (var i = -1; i < 11; i++) {
        html += '<span class="year' + (i === -1 || i === 10 ? ' old' : '') + (currentYear === year ? ' active' : '') + ((year < startYear || year > endYear) ? ' disabled' : '') + '">' + year + '</span>';
        year += 1;
      }
      yearCont.html(html);

      
    },
    

    fillHours: function() {
      var table = this.widget.find(
        '.timepicker .timepicker-hours table');
      table.parent().hide();
      var html = '';
      if (this.options.pick12HourFormat) {
        var current = 1;
        for (var i = 0; i < 3; i += 1) {
          html += '<tr>';
          for (var j = 0; j < 4; j += 1) {
             var c = current.toString();
             html += '<td class="hour">' + padLeft(c, 2, '0') + '</td>';
             current++;
          }
          html += '</tr>'
        }
      } else {
        var current = 0;
        for (var i = 0; i < 6; i += 1) {
          html += '<tr>';
          for (var j = 0; j < 4; j += 1) {
             var c = current.toString();
             html += '<td class="hour">' + padLeft(c, 2, '0') + '</td>';
             current++;
          }
          html += '</tr>'
        }
      }
      table.html(html);
    },

    fillMinutes: function() {
      var table = this.widget.find(
        '.timepicker .timepicker-minutes table');
      table.parent().hide();
      var html = '';
      var current = 0;
      for (var i = 0; i < 3; i++) {
        html += '<tr>';
        for (var j = 0; j < 4; j += 1) {
          var c = current.toString();
          html += '<td class="minute">' + padLeft(c, 2, '0') + '</td>';
          current += 5;
        }
        html += '</tr>';
      }
      table.html(html);
    },

    fillSeconds: function() {
      var table = this.widget.find(
        '.timepicker .timepicker-seconds table');
      table.parent().hide();
      var html = '';
      var current = 0;
      for (var i = 0; i < 3; i++) {
        html += '<tr>';
        for (var j = 0; j < 4; j += 1) {
          var c = current.toString();
          html += '<td class="second">' + padLeft(c, 2, '0') + '</td>';
          current += 5;
        }
        html += '</tr>';
      }
      table.html(html);
    },

    fillTime: function() {
      if (!this._date)
        return;
      var timeComponents = this.widget.find('.timepicker span[data-time-component]');
      var table = timeComponents.closest('table');
      var is12HourFormat = this.options.pick12HourFormat;
      var hour = this._date.getUTCHours();
      var period = 'AM';
      if (is12HourFormat) {
        if (hour >= 12) period = 'PM';
        if (hour === 0) hour = 12;
        else if (hour != 12) hour = hour % 12;
        this.widget.find(
          '.timepicker [data-action=togglePeriod]').text(period);
      }
      hour = padLeft(hour.toString(), 2, '0');
      var minute = padLeft(this._date.getUTCMinutes().toString(), 2, '0');
      var second = padLeft(this._date.getUTCSeconds().toString(), 2, '0');
      timeComponents.filter('[data-time-component=hours]').text(hour);
      timeComponents.filter('[data-time-component=minutes]').text(minute);
      timeComponents.filter('[data-time-component=seconds]').text(second);
    },

    click: function(e) {
      e.stopPropagation();
      e.preventDefault();
      this._unset = false;
      var target = $(e.target).closest('span, td, th');
      if (target.length === 1) {
        if (! target.is('.disabled')) {
          switch(target[0].nodeName.toLowerCase()) {
            case 'th':
              switch(target[0].className) {
                case 'switch':
                  this.showMode(1);
                  break;
                case 'prev':
                case 'next':
                  var vd = this.viewDate;
                  var navFnc = DPGlobal.modes[this.viewMode].navFnc;
                  var step = DPGlobal.modes[this.viewMode].navStep;
                  if (target[0].className === 'prev') step = step * -1;
                  vd['set' + navFnc](vd['get' + navFnc]() + step);
                  this.fillDate();
                  this.set();
                  break;
              }
              break;
            case 'span':
              if (target.is('.month')) {
                var month = target.parent().find('span').index(target);
                this.viewDate.setUTCMonth(month);
              } else {
                var year = parseInt(target.text(), 10) || 0;
                this.viewDate.setUTCFullYear(year);
              }
              if (this.viewMode !== 0) {
                this._date = UTCDate(
                  this.viewDate.getUTCFullYear(),
                  this.viewDate.getUTCMonth(),
                  this.viewDate.getUTCDate(),
                  this._date.getUTCHours(),
                  this._date.getUTCMinutes(),
                  this._date.getUTCSeconds(),
                  this._date.getUTCMilliseconds()
                );
                this.notifyChange();
              }
              if(this.minViewMode === this.viewMode && !this.pickTime){
                this.hide();
              }
              this.showMode(-1);
              this.fillDate();
              this.set();
              // 
              break;
            case 'td':
              if (target.is('.day')) {
                var day = parseInt(target.text(), 10) || 1;
                var month = this.viewDate.getUTCMonth();
                var year = this.viewDate.getUTCFullYear();
                if (target.is('.old')) {
                  if (month === 0) {
                    month = 11;
                    year -= 1;
                  } else {
                    month -= 1;
                  }
                } else if (target.is('.new')) {
                  if (month == 11) {
                    month = 0;
                    year += 1;
                  } else {
                    month += 1;
                  }
                }
                this._date = UTCDate(
                  year, month, day,
                  this._date.getUTCHours(),
                  this._date.getUTCMinutes(),
                  this._date.getUTCSeconds(),
                  this._date.getUTCMilliseconds()
                );
                this.viewDate = UTCDate(
                  year, month, Math.min(28, day) , 0, 0, 0, 0);
                if(!this.range) {
                  this.fillDate();
                  this.set();
                  this.notifyChange();
                  // 只选择日期时，选完后自动关闭
                  if(!this.pickTime) {
                    this.hide();
                  } else {
                    $(".accordion-toggle", this.widget).trigger("click.togglePicker");
                  }
                // date range select
                } else {
                  if(!this._rangestart) {
                    this._lastDate = null;
                    this.fillDate();
                    this._lastDate = UTCDate(
                      this._date.getUTCFullYear(),
                      this._date.getUTCMonth(),
                      this._date.getUTCDate(),
                      0, 0, 0, 0
                    );
                    this._rangestart = true;
                  } else {
                    this.fillDate();
                    this.set(); 
                    this.notifyChange();
                    this._rangestart = false;
                  }

                }
              }
              break;
          }
        }
      }
    },

    actions: {
      incrementHours: function(e) {
        this._date.setUTCHours(this._date.getUTCHours() + 1);
      },

      incrementMinutes: function(e) {
        this._date.setUTCMinutes(this._date.getUTCMinutes() + 1);
      },

      incrementSeconds: function(e) {
        this._date.setUTCSeconds(this._date.getUTCSeconds() + 1);
      },

      decrementHours: function(e) {
        this._date.setUTCHours(this._date.getUTCHours() - 1);
      },

      decrementMinutes: function(e) {
        this._date.setUTCMinutes(this._date.getUTCMinutes() - 1);
      },

      decrementSeconds: function(e) {
        this._date.setUTCSeconds(this._date.getUTCSeconds() - 1);
      },

      togglePeriod: function(e) {
        var hour = this._date.getUTCHours();
        if (hour >= 12) hour -= 12;
        else hour += 12;
        this._date.setUTCHours(hour);
      },

      showPicker: function() {
        this.widget.find('.timepicker > div:not(.timepicker-picker)').hide();
        this.widget.find('.timepicker .timepicker-picker').show();
      },

      showHours: function() {
        this.widget.find('.timepicker .timepicker-picker').hide();
        this.widget.find('.timepicker .timepicker-hours').show();
      },

      showMinutes: function() {
        this.widget.find('.timepicker .timepicker-picker').hide();
        this.widget.find('.timepicker .timepicker-minutes').show();
      },

      showSeconds: function() {
        this.widget.find('.timepicker .timepicker-picker').hide();
        this.widget.find('.timepicker .timepicker-seconds').show();
      },

      selectHour: function(e) {
        var tgt = $(e.target);
        var value = parseInt(tgt.text(), 10);
        if (this.options.pick12HourFormat) {
          var current = this._date.getUTCHours();
          if (current >= 12) {
            if (value != 12) value = (value + 12) % 24;
          } else {
            if (value === 12) value = 0;
            else value = value % 12;
          }
        }
        this._date.setUTCHours(value);
        this.actions.showPicker.call(this);
      },

      selectMinute: function(e) {
        var tgt = $(e.target);
        var value = parseInt(tgt.text(), 10);
        this._date.setUTCMinutes(value);
        this.actions.showPicker.call(this);
      },

      selectSecond: function(e) {
        var tgt = $(e.target);
        var value = parseInt(tgt.text(), 10);
        this._date.setUTCSeconds(value);
        this.actions.showPicker.call(this);
      },

      saveTime: function(e){
        this.hide();
      }
    },

    doAction: function(e) {
      e.stopPropagation();
      e.preventDefault();
      if (!this._date) this._date = UTCDate(1970, 0, 0, 0, 0, 0, 0);
      var action = $(e.currentTarget).data('action');
      var rv = this.actions[action].apply(this, arguments);
      this.set();
      this.fillTime();
      this.notifyChange();
      return rv;
    },

    stopEvent: function(e) {
      e.stopPropagation();
      e.preventDefault();
    },

    // part of the following code was taken from
    // http://cloud.github.com/downloads/digitalBush/jquery.maskedinput/jquery.maskedinput-1.3.js
    keydown: function(e) {
      var self = this, k = e.which, input = $(e.target);
      if (k == 8 || k == 46) {
        // backspace and delete cause the maskPosition
        // to be recalculated
        setTimeout(function() {
          self._resetMaskPos(input);
        });
      }
    },

    keypress: function(e) {
      var k = e.which;
      if (k == 8 || k == 46) {
        // For those browsers which will trigger
        // keypress on backspace/delete
        return;
      }
      var input = $(e.target);
      var c = String.fromCharCode(k);
      var val = input.val() || '';
      val += c;
      var mask = this._mask[this._maskPos];
      if (!mask) {
        return false;
      }
      if (mask.end != val.length) {
        return;
      }
      if (!mask.pattern.test(val.slice(mask.start))) {
        val = val.slice(0, val.length - 1);
        while ((mask = this._mask[this._maskPos]) && mask.character) {
          val += mask.character;
          // advance mask position past static
          // part
          this._maskPos++;
        }
        val += c;
        if (mask.end != val.length) {
          input.val(val);
          return false;
        } else {
          if (!mask.pattern.test(val.slice(mask.start))) {
            input.val(val.slice(0, mask.start));
            return false;
          } else {
            input.val(val);
            this._maskPos++;
            return false;
          }
        }
      } else {
        this._maskPos++;
      }
    },

    change: function(e) {
      var input = $(e.target);
      var pattern = this.range ? this._formatRangePattern : this._formatPattern;
      var val = input.val();
      if (pattern.test(val)) {
        this.update();
        this.range ? this.setValue(val) : this.setValue(this._date.getTime());
        this.notifyChange();
        this.set();
      } else if (val && val.trim()) {
        this.range ? this.setValue(val) : this.setValue(this._date.getTime());
        if (this._date) this.set();
        else input.val('');
      } else {
        if (this._date) {
          this.setValue(null);
          // unset the date when the input is
          // erased
          this.notifyChange();
          this._unset = true;
        }
      }
      this._resetMaskPos(input);
    },

    showMode: function(dir) {
      if (dir) {
        this.viewMode = Math.max(this.minViewMode, Math.min(
          2, this.viewMode + dir));
      }
      this.widget.find('.datepicker > div').hide().filter(
        '.datepicker-'+DPGlobal.modes[this.viewMode].clsName).show();
    },

    destroy: function() {
      this._detachDatePickerEvents();
      this._detachDatePickerGlobalEvents();
      this.widget.remove();
      this.$element.removeData('datetimepicker');
      if(this.component && this.component.length) {
        this.component.removeData('datetimepicker');
      }
    },

    formatDate: function(d) {
      return this.format.replace(formatReplacer, function(match) {
        var methodName, property, rv, len = match.length;
        if (match === 'ms')
          len = 1;
        property = dateFormatComponents[match].property
        if (property === 'Hours12') {
          rv = d.getUTCHours();
          if (rv === 0) rv = 12;
          else if (rv !== 12) rv = rv % 12;
        } else if (property === 'Period12') {
          if (d.getUTCHours() >= 12) return 'PM';
          else return 'AM';
	} else if (property === 'UTCYear') {
          rv = d.getUTCFullYear();
          rv = rv.toString().substr(2);   
        } else {
          methodName = 'get' + property;
          rv = d[methodName]();
        }
        if (methodName === 'getUTCMonth') rv = rv + 1;
        return padLeft(rv.toString(), len, '0');
      });
    },

    parseDate: function(str) {
      var match, i, property, methodName, value, parsed = {};
      if (!(match = this._formatPattern.exec(str)))
        return null;
      for (i = 1; i < match.length; i++) {
        property = this._propertiesByIndex[i];
        if (!property)
          continue;
        value = match[i];
        if (/^\d+$/.test(value))
          value = parseInt(value, 10);
        parsed[property] = value;
      }
      return this._finishParsingDate(parsed);
    },

    _resetMaskPos: function(input) {
      var val = input.val();
      for (var i = 0; i < this._mask.length; i++) {
        if (this._mask[i].end > val.length) {
          // If the mask has ended then jump to
          // the next
          this._maskPos = i;
          break;
        } else if (this._mask[i].end === val.length) {
          this._maskPos = i + 1;
          break;
        }
      }
    },

    _finishParsingDate: function(parsed) {
      var year, month, date, hours, minutes, seconds, milliseconds;
      year = parsed.UTCFullYear;
      if (parsed.UTCYear) year = 2000 + parsed.UTCYear;
      if (!year) year = 1970;
      if (parsed.UTCMonth) month = parsed.UTCMonth - 1;
      else month = 0;
      date = parsed.UTCDate || 1;
      hours = parsed.UTCHours || 0;
      minutes = parsed.UTCMinutes || 0;
      seconds = parsed.UTCSeconds || 0;
      milliseconds = parsed.UTCMilliseconds || 0;
      if (parsed.Hours12) {
        hours = parsed.Hours12;
      }
      if (parsed.Period12) {
        if (/pm/i.test(parsed.Period12)) {
          if (hours != 12) hours = (hours + 12) % 24;
        } else {
          hours = hours % 12;
        }
      }
      return UTCDate(year, month, date, hours, minutes, seconds, milliseconds);
    },

    _compileFormat: function () {
      var match, component, components = [], mask = [],
      str = this.format, propertiesByIndex = {}, i = 0, pos = 0;
      while (match = formatComponent.exec(str)) {
        component = match[0];
        if (component in dateFormatComponents) {
          i++;
          propertiesByIndex[i] = dateFormatComponents[component].property;
          components.push('\\s*' + dateFormatComponents[component].getPattern(
            this) + '\\s*');
          mask.push({
            pattern: new RegExp(dateFormatComponents[component].getPattern(
              this)),
            property: dateFormatComponents[component].property,
            start: pos,
            end: pos += component.length
          });
        }
        else {
          components.push(escapeRegExp(component));
          mask.push({
            pattern: new RegExp(escapeRegExp(component)),
            character: component,
            start: pos,
            end: ++pos
          });
        }
        str = str.slice(component.length);
      }
      this._mask = mask;
      this._maskPos = 0;
      this._formatPattern = new RegExp(
        '^\\s*' + components.join('') + '\\s*$');
      this._formatRangePattern = new RegExp(
        '^\\s*' + components.join('') + " - " + components.join('') + '\\s*$')
      this._propertiesByIndex = propertiesByIndex;
    },

    _attachDatePickerEvents: function() {
      var self = this;
      // this handles date picker clicks
      this.widget.on('click', '.datepicker *', $.proxy(this.click, this));
      // this handles time picker clicks
      this.widget.on('click', '[data-action]', $.proxy(this.doAction, this));
      this.widget.on('mousedown', $.proxy(this.stopEvent, this));
      if (this.pickDate && this.pickTime) {
        this.widget.on('click.togglePicker', '.accordion-toggle', function(e) {
          e.stopPropagation();
          var $this = $(this);
          var $parent = $this.closest('ul');
          var expanded = $parent.find('.collapse.in');
          var closed = $parent.find('.collapse:not(.in)');

          if (expanded && expanded.length) {
            var collapseData = expanded.data('collapse');
            if (collapseData && collapseData.transitioning) return;
            expanded.collapse('hide');
            closed.collapse('show')


            $this.html(self.formatDate(self._date).split(" ")[+!!expanded.find(".timepicker").length])

            // $this.find('i').toggleClass(self.timeIcon + ' ' + self.dateIcon);
            // self.$element.find('.add-on i').toggleClass(self.timeIcon + ' ' + self.dateIcon);
          }
        });
      }


      if (this.isInput) {
        this.$element.on({
          'focus': $.proxy(this.show, this),
          'change': $.proxy(this.change, this)
        });
        if (this.options.maskInput) {
          this.$element.on({
            'keydown': $.proxy(this.keydown, this),
            'keypress': $.proxy(this.keypress, this)
          });
        }
      } else {
        this.$element.on({
          'change': $.proxy(this.change, this)
        }, 'input');
        if (this.options.maskInput) {
          this.$element.on({
            'keydown': $.proxy(this.keydown, this),
            'keypress': $.proxy(this.keypress, this)
          }, 'input');
        }
        if (this.component){
          this.component.on('click', $.proxy(this.show, this));
        } else {
          this.$element.on('click', $.proxy(this.show, this));
        }
      }
    },

    _attachDatePickerGlobalEvents: function() {
      $(window).on(
        'resize.datetimepicker' + this.id, $.proxy(this.place, this));
      if (!this.isInput) {
        $(document).on(
          'mousedown.datetimepicker' + this.id, $.proxy(this.hide, this));
      }
    },

    _detachDatePickerEvents: function() {
      this.widget.off('click', '.datepicker *', this.click);
      this.widget.off('click', '[data-action]');
      this.widget.off('mousedown', this.stopEvent);
      if (this.pickDate && this.pickTime) {
        this.widget.off('click.togglePicker');
      }
      if (this.isInput) {
        this.$element.off({
          'focus': this.show,
          'change': this.change
        });
        if (this.options.maskInput) {
          this.$element.off({
            'keydown': this.keydown,
            'keypress': this.keypress
          });
        }
      } else {
        this.$element.off({
          'change': this.change
        }, 'input');
        if (this.options.maskInput) {
          this.$element.off({
            'keydown': this.keydown,
            'keypress': this.keypress
          }, 'input');
        }
        if (this.component){
          this.component.off('click', this.show);
        } else {
          this.$element.off('click', this.show);
        }
      }
    },

    _detachDatePickerGlobalEvents: function () {
      $(window).off('resize.datetimepicker' + this.id);
      if (!this.isInput) {
        $(document).off('mousedown.datetimepicker' + this.id);
      }
    },

    _isInFixed: function() {
      if (this.$element) {
        var parents = this.$element.parents();
        var inFixed = false;
        for (var i=0; i<parents.length; i++) {
            if ($(parents[i]).css('position') == 'fixed') {
                inFixed = true;
                break;
            }
        };
        return inFixed;
      } else {
        return false;
      }
    }
  };

  $.fn.datetimepicker = function ( option, val ) {
    return this.each(function () {
      var $this = $(this),
      data = $this.data('datetimepicker'),
      options = typeof option === 'object' && option;
      if (!data) {
        $this.data('datetimepicker', (data = new DateTimePicker(
          this, $.extend({}, $.fn.datetimepicker.defaults,options))));
      }
      if (typeof option === 'string') data[option](val);
    });
  };

  $.fn.datetimepicker.defaults = {
    maskInput: false,
    pickDate: true,
    pickTime: true,
    pick12HourFormat: false,
    pickSeconds: true,
    startDate: -Infinity,
    endDate: Infinity,
    collapse: true
  };
  $.fn.datetimepicker.Constructor = DateTimePicker;
  var dpgId = 0;
  var dates = $.fn.datetimepicker.dates = {
    en: {
      days: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday",
        "Friday", "Saturday", "Sunday"],
      daysShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
      daysMin: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa", "Su"],
      months: ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"],
      monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul",
        "Aug", "Sep", "Oct", "Nov", "Dec"]
    }
  };

  var dateFormatComponents = {
    dd: {property: 'UTCDate', getPattern: function() { return '(0?[1-9]|[1-2][0-9]|3[0-1])\\b';}},
    mm: {property: 'UTCMonth', getPattern: function() {return '(0?[1-9]|1[0-2])\\b';}},
    yy: {property: 'UTCYear', getPattern: function() {return '(\\d{2})\\b'}},
    yyyy: {property: 'UTCFullYear', getPattern: function() {return '(\\d{4})\\b';}},
    hh: {property: 'UTCHours', getPattern: function() {return '(0?[0-9]|1[0-9]|2[0-3])\\b';}},
    ii: {property: 'UTCMinutes', getPattern: function() {return '(0?[0-9]|[1-5][0-9])\\b';}},
    ss: {property: 'UTCSeconds', getPattern: function() {return '(0?[0-9]|[1-5][0-9])\\b';}},
    ms: {property: 'UTCMilliseconds', getPattern: function() {return '([0-9]{1,3})\\b';}},
    HH: {property: 'Hours12', getPattern: function() {return '(0?[1-9]|1[0-2])\\b';}},
    PP: {property: 'Period12', getPattern: function() {return '(AM|PM|am|pm|Am|aM|Pm|pM)\\b';}}
  };

  var keys = [];
  for (var k in dateFormatComponents) keys.push(k);
  keys[keys.length - 1] += '\\b';
  keys.push('.');

  var formatComponent = new RegExp(keys.join('\\b|'));
  keys.pop();
  var formatReplacer = new RegExp(keys.join('\\b|'), 'g');

  function escapeRegExp(str) {
    // http://stackoverflow.com/questions/3446170/escape-string-for-use-in-javascript-regex
    return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
  }

  function padLeft(s, l, c) {
    if (l < s.length) return s;
    else return Array(l - s.length + 1).join(c || ' ') + s;
  }

  function getTemplate(timeIcon, pickDate, pickTime, is12Hours, showSeconds, collapse, range) {
    // range = true; 
    if (pickDate && pickTime) {
      return (
        '<div class="bootstrap-datetimepicker-widget dropdown-menu">' +
          '<ul>' +
            '<li' + (collapse ? ' class="collapse in"' : '') + '>' +
              '<div class="datepicker">' +
                DPGlobal.template +
              '</div>' +
            '</li>' +
            '<li class="picker-switch accordion-toggle btn"><a><i class="' + timeIcon + '"></i></a></li>' +
            '<li' + (collapse ? ' class="collapse"' : '') + '>' +
              '<div class="timepicker">' +
                TPGlobal.getTemplate(is12Hours, showSeconds) +
              '</div>' +
              '<div class="picker-switch btn" data-action="saveTime">OK</div>' +
            '</li>' +
          '</ul>' +
        '</div>'
      );
    } else if (pickTime) {
      return (
        '<div class="bootstrap-datetimepicker-widget dropdown-menu">' +
          '<div class="timepicker">' +
            TPGlobal.getTemplate(is12Hours, showSeconds) +
          '</div>' +
          '<div class="picker-switch btn" data-action="saveTime">OK</div>' +
        '</div>'
      );
    } else {
      return (
        '<div class="bootstrap-datetimepicker-widget dropdown-menu">' +
          '<div class="datepicker">' +
            DPGlobal.template +
            // (range ? DPGlobal.rangeTemplate : DPGlobal.template) +
          '</div>' +
        '</div>'
      );
    }
  }

  function UTCDate() {
    return new Date(Date.UTC.apply(Date, arguments));
  }

  var DPGlobal = {
    modes: [
      {
      clsName: 'days',
      navFnc: 'UTCMonth',
      navStep: 1
    },
    {
      clsName: 'months',
      navFnc: 'UTCFullYear',
      navStep: 1
    },
    {
      clsName: 'years',
      navFnc: 'UTCFullYear',
      navStep: 10
    }],
    isLeapYear: function (year) {
      return (((year % 4 === 0) && (year % 100 !== 0)) || (year % 400 === 0))
    },
    getDaysInMonth: function (year, month) {
      return [31, (DPGlobal.isLeapYear(year) ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][month]
    },
    headTemplate:
      '<thead>' +
        '<tr>' +
          '<th class="prev"></th>' +
          '<th colspan="5" class="switch"></th>' +
          '<th class="next"></th>' +
        '</tr>' +
      '</thead>',

    contTemplate: '<tbody><tr><td colspan="7"></td></tr></tbody>'
  };
  DPGlobal.template =
    '<div class="datepicker-days">' +
      '<table class="table-condensed">' +
        DPGlobal.headTemplate +
        '<tbody></tbody>' +
      '</table>' +
    '</div>' +
    '<div class="datepicker-months">' +
      '<table class="table-condensed">' +
        DPGlobal.headTemplate +
        DPGlobal.contTemplate+
      '</table>'+
    '</div>'+
    '<div class="datepicker-years">'+
      '<table class="table-condensed">'+
        DPGlobal.headTemplate+
        DPGlobal.contTemplate+
      '</table>'+
    '</div>';

  var TPGlobal = {
    hourTemplate: '<span data-action="showHours" data-time-component="hours" class="timepicker-hour btn"></span>',
    minuteTemplate: '<span data-action="showMinutes" data-time-component="minutes" class="timepicker-minute btn"></span>',
    secondTemplate: '<span data-action="showSeconds" data-time-component="seconds" class="timepicker-second btn"></span>'
  };
  TPGlobal.getTemplate = function(is12Hours, showSeconds) {
    return (
    '<div class="timepicker-picker">' +
      '<table class="table-condensed"' +
        (is12Hours ? ' data-hour-format="12"' : '') +
        '>' +
        '<tr>' +
          '<td><a href="#" class="timepicker-up" data-action="incrementHours"></a></td>' +
          '<td class="separator"></td>' +
          '<td><a href="#" class="timepicker-up" data-action="incrementMinutes"></a></td>' +
          (showSeconds ?
          '<td class="separator"></td>' +
          '<td><a href="#" class="timepicker-up" data-action="incrementSeconds"></a></td>': '')+
          (is12Hours ? '<td class="separator"></td>' : '') +
        '</tr>' +
        '<tr>' +
          '<td>' + TPGlobal.hourTemplate + '</td> ' +
          '<td class="separator">:</td>' +
          '<td>' + TPGlobal.minuteTemplate + '</td> ' +
          (showSeconds ?
          '<td class="separator">:</td>' +
          '<td>' + TPGlobal.secondTemplate + '</td>' : '') +
          (is12Hours ?
          '<td class="separator"></td>' +
          '<td>' +
          '<button type="button" class="btn btn-primary" data-action="togglePeriod"></button>' +
          '</td>' : '') +
        '</tr>' +
        '<tr>' +
          '<td><a href="#" class="timepicker-down" data-action="decrementHours"></a></td>' +
          '<td class="separator"></td>' +
          '<td><a href="#" class="timepicker-down" data-action="decrementMinutes"></a></td>' +
          (showSeconds ?
          '<td class="separator"></td>' +
          '<td><a href="#" class="timepicker-down" data-action="decrementSeconds"></a></td>': '') +
          (is12Hours ? '<td class="separator"></td>' : '') +
        '</tr>' +
      '</table>' +
    '</div>' +
    '<div class="timepicker-hours" data-action="selectHour">' +
      '<table class="table-condensed">' +
      '</table>'+
    '</div>'+
    '<div class="timepicker-minutes" data-action="selectMinute">' +
      '<table class="table-condensed">' +
      '</table>'+
    '</div>'+
    (showSeconds ?
    '<div class="timepicker-seconds" data-action="selectSecond">' +
      '<table class="table-condensed">' +
      '</table>'+
    '</div>': '')
    );
  }


})(window.jQuery)

$.fn.datetimepicker.dates['zh-CN'] = {
		days: ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六", "星期日"],
		daysShort: ["周日", "周一", "周二", "周三", "周四", "周五", "周六", "周日"],
		daysMin:  ["日", "一", "二", "三", "四", "五", "六", "日"],
		months: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
		monthsShort: ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12"],
		// monthsShort: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
		today: "今日",
		selectMonth: "选择月份",
		selectYear: "选择年份"
};
$.fn.datepicker = function(options){
	var opt = {},
		argu = arguments;
	if(typeof options !== "string") {
		opt = $.extend({
			language: "zh-CN",
			format: "yyyy-mm-dd",
			orientation: "left",
			pickTime: false,
			autoNext: true
		}, options);
	}

	return this.each(function(){
		var $elem = $(this),
			$cp,
			$tecp,
			$targetElem;

		if(!$elem.data("datetimepicker")) {
			$cp = $elem.find(".datepicker-btn");

			$elem.datetimepicker($.extend({
				component: $cp.length ? $cp : false
			}, opt));
			
			$targetElem = Dom.getElem(opt.target, true);
	
			// 当 target jquery对象存在时，创建日期范围组，即会对可选范围做动态限制
			if($targetElem && $targetElem.length) {
				$tecp = $targetElem.find(".datepicker-btn");
				$targetElem.datetimepicker($.extend({
					// 初始化可选时间范围
					startDate: new Date($elem.find(">input").val()),
					component: $tecp.length ? $tecp : false
				}, opt));

				if($targetElem.val()) {
					$elem.datetimepicker("setEndDate", $targetElem.data("datetimepicker").getDate());
				} 
				if($elem.val()) {
					$targetElem.datetimepicker("setStartDate", $elem.data("datetimepicker").getDate());
				}

				// 时间变更时，改变可选时间范围
				$elem.on("changeDate", function(evt){				
					$targetElem.datetimepicker("setStartDate", evt.date);
				});

				$targetElem.on("changeDate", function(evt){				
					$elem.datetimepicker("setEndDate", evt.date);
				});

				// 选择完开始日期后，自动打开结束日期选择器
				if(opt.autoNext){
					var initDate;
					$elem.on("show", function(evt){
						initDate = $(this).data("date");
					})
					$elem.on("hide", function(evt){
						if($(this).data("date") !== initDate){
							$targetElem.datetimepicker("show");
						}
					})
				}
			}
		}
			 
		if(typeof options === "string") {
			$.fn.datetimepicker.apply($(this), argu)
		}
	})
}
/**
 * 基于jquery.select2扩展的select插件，基本使用请参考select2相关文档
 * 默认是多选模式，并提供了input模式下的初始化方法，对应的数据格式是{ id: 1, text: "Hello" } 
 * 这里的参数只对扩展的部分作介绍 
 * filter、includes、excludes、query四个参数是互斥的，理论只能有其一个参数
 * @method ibosSelect
 * @param option.filter
 * @param {Function} option.filter   用于过滤源数据的函数
 * @param {Array} 	 option.includes 用于过滤源数据的数据，有效数据的id组
 * @param {Array} 	 option.excludes 用于过滤源数据的数据，无效数据的id组
 * @param {Boolean}  option.pinyin   启用拼音搜索，需要pinyinEngine组件	
 * @return {jQuery} 
 */
$.fn.ibosSelect = (function(){
	var _process = function(datum, collection, filter){
		var group, attr;
		datum = datum[0];
		if (datum.children) {
			group = {};
			for (attr in datum) {
				if (datum.hasOwnProperty(attr)) group[attr] = datum[attr];
			}
			group.children = [];
			$(datum.children).each2(function(i, childDatum) {
				_process(childDatum, group.children, filter);
			});
			if (group.children.length) {
				collection.push(group);
			}
		} else {
			if(filter && !filter(datum)) {
				return false;
			}
			collection.push(datum);				
		}
	}
	// 使用带有filter过滤源数据的query函数，其实质就是在query函数执行之前，用filter函数先过滤一次数据
	var _queryWithFilter = function(query, filter){
		var t = query.term, filtered = { results: [] }, data = [];

		$(this.data).each2(function(i, datum) {
			_process(datum, data, filter);
		});

		if (t === "") {
			query.callback({ results: data });
			return;
		}

		$(data).each2(function(i, datum) {
			_process(datum, filtered.results, function(d){
				return query.matcher(t, d.text + "");
			})
		});

		query.callback(filtered);
	}
	// 根据ID从data数组中获取对应的文本， 主要用于val设置
	var _getTextById = function(id, data){
		// debugger;
		var ret;
		for(var i = 0; i < data.length; i++){
      if( data[i] == undefined ) continue;
			if(data[i].children){
				ret = _getTextById(id, data[i].children);
				if(typeof ret !== "undefined"){
					break;
				}
			} else {
				if(data[i].id + "" === id) {
					ret = data[i].text;
					break;
				}
			}
		}
		return ret;
	}

	var defaults = {
		multiple: true,
		pinyin: true,
		formatResultCssClass: function(data){
			return data.cls;
		},
		formatNoMatches: function(){ return U.lang("S2.NO_MATCHES"); },
		formatSelectionTooBig: function (limit) { return U.lang("S2.SELECTION_TO_BIG", { count: limit}); },
        formatSearching: function () { return U.lang("S2.SEARCHING"); },
        formatInputTooShort: function (input, min) { return U.lang("S2.INPUT_TO_SHORT", { count: min - input.length}); },
        formatLoadMore: function (pageNumber) { return U.lang("S2.LOADING_MORE"); },

		initSelection: function(elem, callback){
			var ins = elem.data("select2"),
				data = ins.opts.data,
				results;

			if(ins.opts.multiple) {
				results = [];
				$.each(elem.val().split(','), function(index, val){
		            results.push({id: val, text: _getTextById(val, data)});
				})
			} else {
				results = {
					id: elem.val(),
					text: _getTextById(elem.val(), data)
				}
			}

	        callback(results);
		}
	}
	var select2 = function(option){
		if(typeof option !== "string") {
			option = $.extend({}, defaults, option);

			// 注意: filter | query | includes | excludes 四个属性是互斥的
			// filter基于query, 而includes、excludes基于filter
			// 优先度 includes > excludes > filter > query
			
			// includes是一个数组，指定源数据中有效数据的ID值，将过滤ID不在此数组中的数据
			if(option.includes && $.isArray(option.includes)){

				option.filter = function(datum){
					return $.inArray(datum.id, option.includes) !== -1;
				}

			// includes是一个数组，指定源数据中无效数据的ID值，将过滤ID在此数组中的数据
			} else if(option.excludes && $.isArray(option.excludes)) {

				option.filter = function(datum){
					return $.inArray(datum.id, option.excludes) === -1;
				}

			}

			// 当有filter属性时，将使用自定义的query方法替代原来的query方法，filter用于从源数据层面上过滤不需要出现的数据
			if(option.filter){
				option.query = function(query) {
					_queryWithFilter(query, option.filter);
				}
			}
			// 使用pinyin搜索引擎
			if(option.pinyin) {
				var _customMatcher = option.matcher;
				option.matcher = function(term){
					if(term === ""){
						return true;
					}
					return Ibos.matchSpell.apply(this, arguments) && 
					(_customMatcher ? _customMatcher.apply(this, arguments) : true);
				}
			}
			
			// 使用 select 元素时，要去掉一部分默认项
			if($(this).is("select")) {
				delete option.multiple;
				delete option.initSelection;
			}
			return $.fn.select2.call(this, option)
		}

		return $.fn.select2.apply(this, arguments)
	}

	return select2;
})();
/**
 * 滑动条，在JqueryUi的滑动条的基础上，添加了可配置的tip提示及简易标尺
 * 具体使用可参考JqUi
 * 需要JqueryUi的slider及Bootstrap的tooltip;
 * 为嘛不使用jqueryUi的tooltip....　
 * @method ibosSlider
 * @todo 由于jqueryUi的插件支持重初始化，此处需考虑重初始化的情况
 *       
 * @param {Object|String} [option] 配置|调用方法，调用方法时第二个参数开始为该方法的参数
 *     @param {Boolean}       [option.tip]  启用提示
 *     @param {Boolean}       [option.scale]  启用标尺
 *     @param {Jquery|Eelement|Selector}        [option.target] 用于放置值的input
 * @param {Function}      [option.tipFormat]  tip文本的格式化函数，传入当前值，要求返回字符串，默认添加"%"
 * @return {Jquery}       Jq对象
 */
$.fn.ibosSlider = function(option){
	// 获取格式化后的tip
	var _getTip = function(value){
		if(!option.tipFormat || typeof option.tipFormat !== "function") {
			return value;
		} else {
			return option.tipFormat.call(null, value);
		}
	}
	var $target,
		defaultValues;

	if(typeof option === "object"){

		if(option.target) {
			$target = option.target === "next" ? this.next() : $(option.target);
			defaultValues = $target.val();
	
			if(!option.value && !option.values && defaultValues) {
				if(option.range === true) {
					option.values = defaultValues.split(",")
				} else {
					option.value = defaultValues;
				}
			}
			if($target && $target.length) {
				this.on("slidechange", function(evt, data){
					$target.val(option.range === true ? data.values.join(",") : data.value);
				})
			}
		}

		// 判断是否存在tooltip方法
		if(option.tip && $.fn.tooltip) {
			// 默认的tipFormat
			option.tipFormat = option.tipFormat || function(value){
				return value + "%";
			}
			// 创建滑动条时，初始化tooltip
			$(this).on("slidecreate", function(){
				var instance = $.data(this, "uiSlider"),
					opt = $(this).slider("option");

				if(option.range === true) {
					instance.handles.each(function(index, h){
						$(h).tooltip({ title: _getTip(opt.values[index]), animation: false });
					})
				} else {
					instance.handle.tooltip({ title: _getTip(opt.value), animation: false })
				}
			// 滑动时，改变tooltip的title值
			}).on("slide", function(evt, data){
				$.attr(data.handle, "data-original-title", _getTip(data.value));
				$(data.handle).tooltip("show");
			})
			.on("slidechange", function(evt, data){
				$.attr(data.handle, "data-original-title", _getTip(data.value));
			})
		}

		if(option.scale) {
			option.scale = +option.scale;
			$(this).on("slidecreate", function(){
				var $elem = $(this),
					option = $elem.slider("option");

				var $wrap = $('<div class="ui-slider-scale"></div>');
				var scaleStep = (option.max - option.min)/ option.scale;
				
				for(var i = 0; i < option.scale + 1; i++) {
					$wrap.append('<span class="ui-slider-scale-step" style="left: ' + 100/option.scale * i + '%">' + (i * scaleStep + option.min) + '</span>');
				}

				$elem.append($wrap).addClass("ui-slider-hasscale");
			})
		}
	}

	return this.slider.apply(this, arguments);
};
/**
 * jGrowl 1.1.1
 *
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 *
 * Written by Stan Lemon <stanlemon@mac.com>
 * Last updated: 2008.08.17
 *
 * jGrowl is a jQuery plugin implementing unobtrusive userland notifications.  These 
 * notifications function similarly to the Growl Framework available for
 * Mac OS X (http://growl.info).
 *
 * To Do:
 * - Move library settings to containers and allow them to be changed per container
 *
 * Changes in 1.1.1
 * - Fixed CSS styling bug for ie6 caused by a mispelling
 * - Changes height restriction on default notifications to min-height
 * - Added skinned examples using a variety of images
 * - Added the ability to customize the content of the [close all] box
 * - Added jTweet, an example of using jGrowl + Twitter
 *
 * Changes in 1.1.0
 * - Multiple container and instances.
 * - Standard $.jGrowl() now wraps $.fn.jGrowl() by first establishing a generic jGrowl container.
 * - Instance methods of a jGrowl container can be called by $.fn.jGrowl(methodName)
 * - Added glue preferenced, which allows notifications to be inserted before or after nodes in the container
 * - Added new log callback which is called before anything is done for the notification
 * - Corner's attribute are now applied on an individual notification basis.
 *
 * Changes in 1.0.4
 * - Various CSS fixes so that jGrowl renders correctly in IE6.
 *
 * Changes in 1.0.3
 * - Fixed bug with options persisting across notifications
 * - Fixed theme application bug
 * - Simplified some selectors and manipulations.
 * - Added beforeOpen and beforeClose callbacks
 * - Reorganized some lines of code to be more readable
 * - Removed unnecessary this.defaults context
 * - If corners plugin is present, it's now customizable.
 * - Customizable open animation.
 * - Customizable close animation.
 * - Customizable animation easing.
 * - Added customizable positioning (top-left, top-right, bottom-left, bottom-right, center)
 *
 * Changes in 1.0.2
 * - All CSS styling is now external.
 * - Added a theme parameter which specifies a secondary class for styling, such
 *   that notifications can be customized in appearance on a per message basis.
 * - Notification life span is now customizable on a per message basis.
 * - Added the ability to disable the global closer, enabled by default.
 * - Added callbacks for when a notification is opened or closed.
 * - Added callback for the global closer.
 * - Customizable animation speed.
 * - jGrowl now set itself up and tears itself down.
 *
 * Changes in 1.0.1:
 * - Removed dependency on metadata plugin in favor of .data()
 * - Namespaced all events
 */
(function($) {

  /** jGrowl Wrapper - Establish a base jGrowl Container for compatibility with older releases. **/
  $.jGrowl = function( m , o ) {
    // To maintain compatibility with older version that only supported one instance we'll create the base container.
    if ( $('#jGrowl').size() == 0 ) $('<div id="jGrowl"></div>').addClass((o && o.position) ? o.position : $.jGrowl.defaults.position).appendTo('body');
    // Create a notification on the container.
    $('#jGrowl').jGrowl(m,o);
  };


  /** Raise jGrowl Notification on a jGrowl Container **/
  $.fn.jGrowl = function( m , o ) {
    if ( $.isFunction(this.each) ) {
      var args = arguments;

      return this.each(function() {
        var self = this;

        /** Create a jGrowl Instance on the Container if it does not exist **/
        if ( $(this).data('jGrowl.instance') == undefined ) {
          $(this).data('jGrowl.instance', new $.fn.jGrowl());
          $(this).data('jGrowl.instance').startup( this );
        }

        /** Optionally call jGrowl instance methods, or just raise a normal notification **/
        if ( $.isFunction($(this).data('jGrowl.instance')[m]) ) {
          $(this).data('jGrowl.instance')[m].apply( $(this).data('jGrowl.instance') , $.makeArray(args).slice(1) );
        } else {
          $(this).data('jGrowl.instance').notification( m , o );
        }
      });
    };
  };

  $.extend( $.fn.jGrowl.prototype , {

    /** Default JGrowl Settings **/
    defaults: {
      header:     '',
      single:         true,
      sticky:     false,
      position:     'center', // Is this still needed?
      glue:       'after',
      theme:      'default',
      corners:    '10px',
      check:      500,
      life:       3000,
      speed:      'normal',
      easing:     'swing',
      closer:     true,
      closerTemplate: '<div>[ 关闭全部 ]</div>',
      log:      function(e,m,o) {},
      beforeOpen:   function(e,m,o) {},
      open:       function(e,m,o) {},
      beforeClose:  function(e,m,o) {},
      close:      function(e,m,o) {},
      animateOpen:  {
        opacity:  'show'
      },
      animateClose:   {
        opacity:  'hide'
      }
    },
    
    /** jGrowl Container Node **/
    element:  null,
  
    /** Interval Function **/
    interval:   null,
    
    /** Create a Notification **/
    notification:   function( message , o ) {
      var self = this;
      var o = $.extend({}, this.defaults, o);

      o.log.apply( this.element , [this.element,message,o] );

      if (o.single){
        $('div.jGrowl-notification').children('div.close').trigger("click.jGrowl");
      }

      var notification = $('<div class="jGrowl-notification"><div class="close">&times;</div><div class="j-header">' + o.header + '</div><div class="message">' + message + '</div></div>')
        .data("jGrowl", o).addClass(o.theme).children('div.close').bind("click.jGrowl", function() {
          if(o.single){
            $(this).unbind('click.jGrowl').parent().trigger('jGrowl.beforeClose').trigger('jGrowl.close').remove();
          }else{
            $(this).unbind('click.jGrowl').parent().trigger('jGrowl.beforeClose').animate(o.animateClose, o.speed, o.easing, function() {
              $(this).trigger('jGrowl.close').remove();
            });
          }
        }).parent();
      
      ( o.glue == 'after' ) ? $('div.jGrowl-notification:last', this.element).after(notification) : $('div.jGrowl-notification:first', this.element).before(notification);

      /** Notification Actions **/
      $(notification).bind("mouseover.jGrowl", function() {
        $(this).data("jGrowl").pause = true;
      }).bind("mouseout.jGrowl", function() {
        $(this).data("jGrowl").pause = false;
      }).bind('jGrowl.beforeOpen', function() {
        o.beforeOpen.apply( self.element , [self.element,message,o] );
      }).bind('jGrowl.open', function() {
        o.open.apply( self.element , [self.element,message,o] );
      }).bind('jGrowl.beforeClose', function() {
        o.beforeClose.apply( self.element , [self.element,message,o] );
      }).bind('jGrowl.close', function() {
        o.close.apply( self.element , [self.element,message,o] );
      }).trigger('jGrowl.beforeOpen').animate(o.animateOpen, o.speed, o.easing, function() {
        $(this).data("jGrowl") && ($(this).data("jGrowl").created = new Date());
      }).trigger('jGrowl.open');
    
      /** Optional Corners Plugin **/
      if ( $.fn.corner != undefined ) $(notification).corner( o.corners );

      /** Add a Global Closer if more than one notification exists **/
      if ( !o.single && $('div.jGrowl-notification:parent', this.element).size() > 1 && $('div.jGrowl-closer', this.element).size() == 0 && this.defaults.closer != false ) {
        $(this.defaults.closerTemplate).addClass('jGrowl-closer').addClass(this.defaults.theme).appendTo(this.element).animate(this.defaults.animateOpen, this.defaults.speed, this.defaults.easing).bind("click.jGrowl", function() {
          $(this).siblings().children('div.close').trigger("click.jGrowl");

          if ( $.isFunction( self.defaults.closer ) ) self.defaults.closer.apply( $(this).parent()[0] , [$(this).parent()[0]] );
        });
      };
    },

    /** Update the jGrowl Container, removing old jGrowl notifications **/
    update:  function() {
      $(this.element).find('div.jGrowl-notification:parent').each( function() {
        if ( $(this).data("jGrowl") != undefined && $(this).data("jGrowl").created != undefined && ($(this).data("jGrowl").created.getTime() + $(this).data("jGrowl").life)  < (new Date()).getTime() && $(this).data("jGrowl").sticky != true && 
           ($(this).data("jGrowl").pause == undefined || $(this).data("jGrowl").pause != true) ) {
          $(this).children('div.close').trigger('click.jGrowl');
        }
      });

      if ( $(this.element).find('div.jGrowl-notification:parent').size() < 2 ) {
        $(this.element).find('div.jGrowl-closer').animate(this.defaults.animateClose, this.defaults.speed, this.defaults.easing, function() {
          $(this).remove();
        });
      };
    },

    /** Setup the jGrowl Notification Container **/
    startup:  function(e) {
      this.element = $(e).addClass('jGrowl').append('<div class="jGrowl-notification"></div>');
      this.interval = setInterval( function() { jQuery(e).data('jGrowl.instance').update(); }, this.defaults.check);
      
      if ($.browser.msie && parseInt($.browser.version) < 7) $(this.element).addClass('ie6');
    },

    /** Shutdown jGrowl, removing it and clearing the interval **/
    shutdown:   function() {
      $(this.element).removeClass('jGrowl').find('div.jGrowl-notification').remove();
      $(this).data('jGrowl.instance', null);
      clearInterval( this.interval );
    }
  });
  
  /** Reference the Defaults Object for compatibility with older versions of jGrowl **/
  $.jGrowl.defaults = $.fn.jGrowl.prototype.defaults;

})(jQuery);
/* checkbox radio初始化 */
(function(window, $) {
	/**
	 * checkbox和radio的美化
	 * @class  Label
	 * @param  {Jquery} $el 目标元素
	 * @return {Object}     Label实例
	 */
	var Label = function($el) {
		var type = $el.attr("type");
		if(!type || (type !== "radio" && type !== "checkbox")) {
			throw new Error('初始化类型必须为"checkbox"或"radio"');
		}
		this.$el = $el;
		this.type = type;
		this.name = $el.attr("name");
		Label.items.push(this);
		this._initLabel();
		this._bindEvent();
	}
	/**
	 * 已初始化项的集合
	 * @type {Array}
	 */
	Label.items = [];
	Label.get = function(filter){
		var ret = [];
		for(var i = 0; i < this.items.lenght; i++) {
			if(filter && filter.call(this, this.items[i])) {
				ret.push(this.items[i]);
			}
		}
		return ret;
	}

	Label.prototype = {
		constructor: Label,
		/**
		 * 初始化checkbox和radio的容器
		 * @method _initLabel
		 * @private 
		 * @chainable
		 * @return {Object} 当前调用对象
		 */
		_initLabel: function() {
			var type = this.type,
				//向上查找css类名和type相同的节点
				$label = this.$el.parents('label.' + type).first();
			//如果不存在目标或该目标元素类型不为'label', 则创建;
			if(!$label.length){
				$label = $('<label>').addClass(type);
				$label.append(this.$el);
			}
			//加入作为样式表现的html
			$label.prepend('<span class="icon"></span><span class="icon-to-fade"></span>');
			this.$label = $label;
			this.refresh();
			return this;
		},
		_refresh: function(){
			this.$el.is(':checked') ? this.$label.addClass('checked') : this.$label.removeClass('checked');
			this.$el.is(':disabled') ? this.$label.addClass('disabled') : this.$label.removeClass('disabled');
		},

		refresh: function(){
			if(this.type === "radio") {
				var items = this.constructor.items;
				for(var i = 0, len = items.length; i < len; i++) {
					if(items[i].name === this.name && items[i].type === this.type) {
						items[i]._refresh();
					}
				}
			} else {
				this._refresh();
			}
		},

		/**
		 * 事件绑定
		 * @method _bindEvent
		 * @private
		 * @chainable
		 * @return {Object} 当前调用对象
		 */
		_bindEvent: function(){
			var that = this;
			this.$el.on('change', function(){
				that.refresh();
			})
		},
		check: function(){
			this.$el.prop('checked', true);
			this.refresh()
		},
		uncheck: function(){
			this.$el.prop('checked', false);
			this.refresh()
		},
		disable: function(){
			this.$el.prop('disabled', true);
			this.$label.addClass('disabled');
		},
		enable: function(){
			this.$el.prop('disabled', false);
			this.$label.removeClass('disabled');
		},
		toggle: function(){
			if(this.$el.prop('checked')) {
				this.uncheck();
			} else {
				this.check();
			}
		},
		toggleDisabled: function(){
			if(this.$el.prop('disabled')) {
				this.enable();
			} else {
				this.disable();
			}
		}
	}

	$.fn.label = function(option){
		var data;
		return this.each(function(){
			data = $(this).data('label');
			if(!data){
				$(this).data('label', data = new Label($(this)));
			}
			if(typeof option === 'string'){
				data[option].call(data);
			}
		})
	}
	$.fn.label.Constructor = Label;

	$(function(){
		$('.checkbox input, .radio input').label();
	})
})(window, window.jQuery);
/* 拼音搜索引擎 */
(function(window){
	var pinyinEngine = window.pinyinEngine = {};
	/**
	 * 支持多音字的汉字转拼音算法
	 * @version	2011-07-22
	 * @see		https://github.com/hotoo/pinyin.js
	 * @author	闲耘, 唐斌
	 * @param	{String}		要转为拼音的目标字符串（汉字）
	 * @param	{Boolean}		是否仅保留匹配的第一个拼音
	 * @param	{String}		返回结果的分隔符，默认返回数组集合
	 * @return	{String, Array} 如果 sp 为 null，则返回 Array
	 *							否则，返回以 sp 分隔的字符串
	 */
	pinyinEngine.toPinyin = function () {
		
		// 笛卡尔乘积，返回两个数组的所有可能的组合
		function product (a, b, sp) {
			var r = [], val, str = [];
			for (var i = 0, l = a.length; i < l; i ++) {
				for (var j = 0, m = b.length; j < m; j ++) {
					val = r[r.length] = (a[i] instanceof Array) ? a[i].concat(b[j]) : [].concat(a[i],b[j]);
					str.push(val.join(""));
				}
			}
			return {
				array: r,
				string: str.join(sp || "")
			};
		}

		return function (keyword, single, sp) {
			var cache = pinyinEngine.cache();
			var len = keyword.length, pys, py, pyl, i, y;
			
			if (len === 0) {return single ? "" : []}
			if (len === 1) {
				y = cache[keyword];
				if (single) {return y && y[0] ? y[0] : keyword}
				return y || [keyword];
			} else {
				py = [];
				for (i = 0; i < len; i ++) {
					y = cache[keyword.charAt(i)];
					if (y) {
						py[py.length] = single ? y[0] : y;
					} else {
						py[py.length] = single ? keyword.charAt(i) : [keyword.charAt(i)];
					}
				}
				if (single) {return sp == null ? py : py.join(sp || "")}

				pys = py[0];
				pyl = py.length;
				var prt, str;
				for (i = 1; i < pyl; i++) {
					prt = product(pys, py[i], sp);
					pys = prt.array;
				}
				return sp == null ? pys : prt.string;
			};
		};
		
	}();

	/**
	 * 汉字拼音索引缓存
	 * @return	{Object}	名为汉字值拼音的对象
	 * @example	var pinyin = pinyinEngine.cache(); pinyin['乐']; // ["le", "yue"]
	 */
	pinyinEngine.cache = function () {
		var cache = {},
			isEmptyObject = true,
			data = pinyinEngine._data || {},
			txts, txt, i, j, m;
		
		for (i in data) {
			isEmptyObject = false;
			txts = data[i];
			j = 0;
			m = txts.length;
			for(; j < m; j ++) {
				txt = txts.charAt(j);
				if (!cache[txt]) {
					cache[txt] = [];
				}
				cache[txt].push(i);
			}
		}
		
		if (!isEmptyObject) {
			pinyinEngine.cache = function () {
				return cache;
			};
		}
		
		return cache;
	};

	// 拼音对照简体
	pinyinEngine._data = {a:"啊阿吖嗄腌锕",ai:"爱埃挨哎唉哀皑癌蔼矮艾碍隘捱嗳嗌嫒瑷暧砹锿霭",an:"安案按鞍氨俺暗岸胺谙埯揞犴庵桉铵鹌黯",ang:"肮昂盎",ao:"凹敖熬翱袄傲奥懊澳坳拗嗷岙廒遨媪骜獒聱螯鏊鳌鏖",ba:"芭捌扒叭吧笆八疤巴拔跋靶把耙坝霸罢爸茇菝岜灞钯粑鲅魃",bai:"白柏百摆佰败拜稗捭掰",ban:"版般办斑班搬扳颁板扮拌伴瓣半绊阪坂钣瘢癍舨",bang:"帮邦梆榜膀绑棒磅蚌镑傍谤蒡浜",bao:"保报包苞胞褒剥薄雹堡饱宝抱暴豹鲍爆炮曝瀑勹葆孢煲鸨褓趵龅",bei:"北备杯碑悲卑辈背贝钡倍狈惫焙被孛陂邶蓓呗悖碚鹎褙鐾鞴",ben:"本奔苯笨夯畚坌锛",beng:"蚌崩绷甭泵蹦迸嘣甏",bi:"比必币毕逼鼻鄙笔彼碧蓖蔽毙毖庇痹闭敝弊辟壁臂避陛匕俾芘荜荸萆薜吡哔狴庳愎滗濞弼妣婢嬖璧贲睥畀铋秕裨筚箅篦舭襞跸髀",bian:"变编边便鞭贬扁卞辨辩辫遍匾弁苄忭汴缏煸砭碥窆褊蝙笾鳊",biao:"表标彪膘婊骠飑飙飚镖镳瘭裱鳔",bie:"别鳖憋瘪蹩",bin:"彬斌濒滨宾摈傧豳缤玢槟殡膑镔髌鬓",bing:"并兵冰柄丙秉饼炳病屏禀邴摒槟",bo:"播博柏剥薄玻菠拨钵波勃搏铂箔伯帛舶脖膊渤泊驳卜亳啵饽檗擘礴钹鹁簸跛踣",bu:"不布部步捕卜哺补埠簿怖埔卟逋瓿晡钚钸醭",ca:"擦嚓礤",cai:"才采材彩猜裁财睬踩菜蔡",can:"参餐蚕残惭惨灿孱骖璨粲黪",cang:"藏苍舱仓沧伧",cao:"操糙槽曹草嘈漕螬艚",ce:"册策测厕侧恻",ceng:"曾层蹭噌",cha:"查插叉茬茶碴搽察岔差诧刹喳嚓猹馇汊姹杈楂槎檫锸镲衩",chai:"差拆柴豺侪钗瘥虿",chan:"产搀掺蝉馋谗缠铲阐颤冁谄蒇廛忏潺澶孱羼婵骣觇禅蟾躔",chang:"场常长昌猖尝偿肠厂敞畅唱倡裳伥鬯苌菖徜怅惝阊娼嫦昶氅鲳",chao:"超抄钞朝嘲潮巢吵炒绰剿怊晁焯耖",che:"车扯撤掣彻澈坼砗",chen:"称郴臣辰尘晨忱沉陈趁衬沈谌谶抻嗔宸琛榇碜龀",cheng:"成程称城撑橙呈乘惩澄诚承逞骋秤丞埕枨柽塍瞠铖铛裎蛏酲",chi:"持吃痴匙池迟弛驰耻齿侈尺赤翅斥炽傺墀茌叱哧啻嗤彳饬媸敕眵鸱瘛褫蚩螭笞篪豉踟魑",chong:"重充冲虫崇宠茺忡憧铳舂艟",chou:"抽酬畴踌稠愁筹仇绸瞅丑臭俦帱惆瘳雠",chu:"出除处础初橱厨躇锄雏滁楚储矗搐触畜亍刍怵憷绌杵楮樗褚蜍蹰黜",chuai:"揣搋啜嘬膪踹",chuan:"传川穿椽船喘串舛遄氚钏舡",chuang:"创疮窗幢床闯怆",chui:"吹炊捶锤垂椎陲棰槌",chun:"春椿醇唇淳纯蠢莼鹑蝽",chuo:"戳绰啜辍踔龊",ci:"次此词差疵茨磁雌辞慈瓷刺赐茈祠鹚糍",cong:"从聪葱囱匆丛苁淙骢琮璁枞",cou:"凑楱辏腠",cu:"促粗醋簇卒蔟徂猝殂酢蹙蹴",cuan:"蹿篡窜攒汆撺爨镩",cui:"摧崔催脆瘁粹淬翠衰萃啐悴璀榱毳",cun:"存村寸忖皴",cuo:"错措磋撮搓挫厝嵯脞锉矬痤瘥鹾蹉",da:"大达打答搭瘩耷哒嗒囗怛妲沓褡笪靼鞑",dai:"代带呆歹傣戴殆贷袋待逮怠埭甙呔岱迨绐玳黛",dan:"单但耽担丹郸掸胆旦氮惮淡诞弹蛋石儋凼萏菪啖澹宕殚赕眈疸瘅聃箪",dang:"当挡党荡档谠砀铛裆",dao:"到道导刀捣蹈倒岛祷稻悼盗叨氘焘纛",de:"的地得德锝",deng:"等登蹬灯瞪凳邓噔嶝戥磴镫簦",di:"的地第底弟堤低滴迪敌笛狄涤翟嫡抵蒂帝递缔氐籴诋谛邸坻荻嘀娣柢棣觌祗砥碲睇镝羝骶",dian:"电点典店颠掂滇碘靛垫佃甸惦奠淀殿丶阽坫巅玷钿癜癫簟踮",diao:"调碉叼雕凋刁掉吊钓铞貂鲷",die:"跌爹碟蝶迭谍叠垤堞揲喋牒瓞耋鲽",ding:"定丁盯叮钉顶鼎锭订仃啶玎腚碇町疔耵酊",diu:"丢铥",dong:"动东冬董懂栋侗恫冻洞垌咚岽峒氡胨胴硐鸫",dou:"都兜抖斗陡豆逗痘蔸钭窦蚪篼",du:"都度读督毒犊独堵睹赌杜镀肚渡妒芏嘟渎椟牍蠹笃髑黩",duan:"断段短端锻缎椴煅簖",dui:"对堆兑队敦怼憝碓镦",dun:"墩吨蹲敦顿囤钝盾遁沌炖砘礅盹趸",duo:"多度掇哆夺垛躲朵跺舵剁惰堕咄哚沲缍铎裰踱",e:"阿蛾峨鹅俄额讹娥恶厄扼遏鄂饿哦噩谔垩苊莪萼呃愕屙婀轭腭锇锷鹗颚鳄",en:"恩蒽摁嗯",er:"而二儿耳尔饵洱贰迩珥铒鸸鲕",fa:"发法罚筏伐乏阀珐垡砝",fan:"范藩帆番翻樊矾钒繁凡烦反返贩犯饭泛蕃蘩幡夂梵攵燔畈蹯",fang:"方放访房坊芳肪防妨仿纺邡枋钫舫鲂",fei:"费非菲啡飞肥匪诽吠肺废沸芾狒悱淝妃绯榧腓斐扉砩镄痱蜚篚翡霏鲱",fen:"分份芬酚吩氛纷坟焚汾粉奋忿愤粪偾瀵棼鲼鼢",feng:"风丰封枫蜂峰锋疯烽逢冯缝讽奉凤俸酆葑唪沣砜",fo:"佛",fou:"否缶",fu:"服复府父负福富夫敷肤孵扶拂辐幅氟符伏俘浮涪袱弗甫抚辅俯釜斧脯腑腐赴副覆赋傅付阜腹讣附妇缚咐莆匐凫郛芙芾苻茯莩菔拊呋呒幞怫滏艴孚驸绂绋桴赙祓黻黼罘稃馥蚨蜉蝠蝮麸趺跗鲋鳆",ga:"噶嘎夹轧垓尬尕尜旮钆",gai:"改该概钙盖溉芥丐陔戤赅",gan:"感敢干甘杆柑竿肝赶秆赣坩苷尴擀泔淦澉绀橄旰矸疳酐",gang:"港冈刚钢缸肛纲岗杠戆罡筻",gao:"告高篙皋膏羔糕搞镐稿睾诰郜藁缟槔槁杲锆",ge:"个格合歌各革哥搁戈鸽胳疙割葛蛤阁隔铬咯鬲仡哿圪塥嗝纥搿膈硌镉袼颌虼舸骼",gei:"给",gen:"根跟亘茛哏艮",geng:"更耕庚羹埂耿梗哽赓绠鲠",gong:"公工供功共贡攻恭龚躬宫弓巩汞拱珙肱蚣觥",gou:"构够购钩勾沟苟狗垢佝诟岣遘媾缑枸觏彀笱篝鞲",gu:"告故辜菇咕箍估沽孤姑鼓古蛊骨谷股顾固雇贾嘏诂菰崮汩梏轱牯牿臌毂瞽罟钴锢鸪鹄痼蛄酤觚鲴鹘",gua:"刮瓜剐寡挂褂卦诖呱栝胍鸹",guai:"乖拐怪掴",guan:"关管观棺官冠馆罐惯灌贯纶倌莞掼涫盥鹳矜鳏",guang:"广光逛咣犷桄胱",gui:"规瑰圭硅归龟闺轨鬼诡癸桂柜跪贵刽匦刿庋宄妫桧炅晷皈簋鲑鳜",gun:"辊滚棍衮绲磙鲧",guo:"国过果锅郭裹馘埚掴呙帼崞猓椁虢聒蜾蝈",ha:"哈蛤铪",hai:"还海孩骸氦亥害骇嗨胲醢",han:"韩酣憨邯含涵寒函喊罕翰撼捍旱憾悍焊汗汉邗菡撖犴阚瀚晗焓顸颔蚶鼾",hang:"行杭夯航吭沆绗颃",hao:"好号壕嚎豪毫郝耗浩貉蒿薅嗥嚆濠灏昊皓颢蚝",he:"何合呵喝荷菏核禾和盒貉阂河涸赫褐鹤贺诃劾壑嗬阖曷盍颌蚵翮",hei:"嘿黑",hen:"很痕狠恨",heng:"哼亨横衡恒蘅珩桁",hong:"轰哄烘虹鸿洪宏弘红黉訇讧荭蕻薨闳泓",hou:"后候喉侯猴吼厚堠後逅瘊篌糇鲎骺",hu:"户护乎互和呼忽瑚壶葫胡蝴狐糊湖弧虎唬沪冱唿囫岵猢怙惚浒滹琥槲轷觳烀煳戽扈祜瓠鹄鹕鹱笏醐斛鹘",hua:"话华化划花哗猾滑画骅桦砉铧",huai:"槐徊怀淮坏踝",huan:"还欢环桓缓换患唤痪豢焕涣宦幻郇奂萑擐圜獾洹浣漶寰逭缳锾鲩鬟",huang:"荒慌黄磺蝗簧皇凰惶煌晃幌恍谎隍徨湟潢遑璜肓癀蟥篁鳇",hui:"会回恢挥灰辉徽蛔毁悔慧卉惠晦贿秽烩汇讳诲绘诙茴荟蕙咴喙隳洄彗缋珲桧晖恚虺蟪麾",hun:"荤昏婚魂浑混诨馄阍溷珲",huo:"活或获和豁伙火惑霍货祸劐藿攉嚯夥钬锪镬耠蠖",ji:"系己机技计级积际及基击记济辑即几继集极给绩激圾畸稽箕肌饥迹讥鸡姬缉吉棘籍急疾汲嫉挤脊蓟冀季伎祭剂悸寄寂既忌妓纪藉骑亟乩剞佶偈墼芨芰荠蒺蕺掎叽咭哜唧岌嵴洎屐骥畿玑楫殛戟戢赍觊犄齑矶羁嵇稷瘠虮笈笄暨跻跽霁鲚鲫髻麂",jia:"家加价佳嘉枷夹荚颊贾甲钾假稼架驾嫁茄伽郏葭岬浃迦珈戛胛恝铗镓痂瘕蛱笳袈跏",jian:"间件建简荐见健检坚监减键歼尖笺煎兼肩艰奸缄茧柬碱硷拣捡俭剪槛鉴践贱箭舰剑饯渐溅涧僭谏谫菅蒹搛囝湔蹇謇缣枧楗戋戬牮犍毽腱睑锏鹣裥笕翦踺鲣鞯",jiang:"江强僵姜将浆疆蒋桨奖讲匠酱降茳洚绛缰犟礓耩糨豇靓",jiao:"教交校较蕉椒礁焦胶郊浇骄娇嚼搅铰矫侥脚狡角饺缴绞剿酵轿叫窖佼僬艽茭挢噍峤徼湫姣敫皎鹪蛟醮跤鲛",jie:"接结解介界阶姐揭皆秸街截劫节桔杰捷睫竭洁戒藉芥借疥诫届讦诘拮喈嗟婕孑桀碣疖颉蚧羯鲒骱",jin:"进今金近仅津尽巾筋斤襟紧锦谨靳晋禁烬浸劲卺荩堇噤馑廑妗缙瑾槿赆觐衿矜",jing:"经京精境睛竞竟荆兢茎晶鲸惊粳井警景颈静敬镜径痉靖净刭儆阱菁獍憬泾迳弪婧肼胫腈旌箐",jiong:"炯窘迥扃",jiu:"就究酒揪纠玖韭久灸九厩救旧臼舅咎疚僦啾阄柩桕鸠鹫赳鬏",ju:"具且据车举巨鞠拘狙疽居驹菊局咀矩沮聚拒距踞锯俱句惧炬剧倨讵苣苴莒掬遽屦琚枸椐榘榉橘犋飓钜锔窭裾趄醵踽龃雎鞫",juan:"捐鹃娟倦眷卷绢鄄狷涓桊蠲锩镌隽",jue:"觉决绝嚼撅攫抉掘倔爵诀厥劂谲矍蕨噘噱崛獗孓珏桷橛爝镢蹶觖",jun:"均菌钧军君峻俊竣浚郡骏捃皲筠麇",ka:"喀咖卡咯佧咔胩",kai:"开揩楷凯慨剀垲蒈忾恺铠锎锴",kan:"看槛刊堪勘坎砍侃莰阚戡龛瞰",kang:"康慷糠扛抗亢炕伉闶钪",kao:"考拷烤靠尻栲犒铐",ke:"可科客课坷苛柯棵磕颗壳咳渴克刻嗑岢恪溘骒缂珂轲氪瞌钶锞稞疴窠颏蝌髁",ken:"肯啃垦恳裉龈",keng:"坑吭铿",kong:"控空恐孔倥崆箜",kou:"口抠扣寇芤蔻叩囗眍筘",ku:"枯哭窟苦酷库裤刳堀喾绔骷",kua:"夸垮挎跨胯侉",kuai:"会快块筷侩蒯郐哙狯浍脍",kuan:"款宽髋",kuang:"况匡筐狂框矿眶旷诓诳邝圹夼哐纩贶",kui:"亏盔岿窥葵奎魁傀馈愧溃馗匮夔隗蒉揆喹喟悝愦逵暌睽聩蝰篑跬",kun:"坤昆捆困悃阃琨锟醌鲲髡",kuo:"括扩廓阔蛞",la:"垃拉喇蜡腊辣啦落剌邋旯砬瘌",lai:"来莱赖崃徕涞濑赉睐铼癞籁",lan:"览蓝婪栏拦篮阑兰澜谰揽懒缆烂滥岚漤榄斓罱镧褴",lang:"琅榔狼廊郎朗浪蒗啷阆稂螂",lao:"老捞劳牢佬姥酪烙涝潦唠崂忉栳铑铹痨耢醪",le:"了乐勒仂叻泐鳓",lei:"类雷镭蕾磊累儡垒擂肋泪羸诔嘞嫘缧檑耒酹",leng:"棱楞冷塄愣",li:"理里力立历离利丽厘梨犁黎篱狸漓李鲤礼莉荔吏栗厉励砾傈例俐痢粒沥隶璃哩俪俚郦坜苈莅蓠藜呖唳喱猁溧澧逦娌嫠骊缡枥栎轹膦戾砺詈罹锂鹂疠疬蛎蜊蠡笠篥粝醴跞雳鲡鳢黧",lia:"俩",lian:"联链连练脸莲镰廉怜涟帘敛恋炼蔹奁潋濂琏楝殓臁裢裣蠊鲢",liang:"量两良亮俩粮凉梁粱辆晾谅墚莨椋锒踉靓魉",liao:"了料疗撩聊僚燎寥辽潦撂镣廖蓼尥嘹獠寮缭钌鹩",lie:"列裂烈劣猎冽埒捩咧洌趔躐鬣",lin:"琳林磷霖临邻鳞淋凛赁吝拎蔺啉嶙廪懔遴檩辚瞵粼躏麟",ling:"领另铃令玲菱零龄伶羚凌灵陵岭酃苓呤囹泠绫柃棂瓴聆蛉翎鲮",liu:"留浏流溜琉榴硫馏刘瘤柳六遛骝绺旒熘锍镏鹨鎏",long:"龙聋咙笼窿隆垄拢陇垅茏泷珑栊胧砻癃",lou:"楼娄搂篓漏陋露偻蒌喽嵝镂瘘耧蝼髅",lu:"录陆芦卢颅庐炉掳卤虏鲁麓碌露路赂鹿潞禄戮垆撸噜泸渌漉逯璐栌橹轳辂辘氇胪镥鸬鹭簏舻鲈",lv:"律旅虑驴吕铝侣履屡缕氯率滤绿偻捋闾榈膂稆褛",luan:"峦挛孪滦卵乱脔娈栾鸾銮",lue:"略掠锊",lun:"论抡轮伦仑沦纶囵",luo:"络咯烙萝螺罗逻锣箩骡裸落洛骆倮蠃荦摞猡泺漯珞椤脶镙瘰跞雒",ma:"码妈马麻玛蚂骂嘛吗抹唛犸杩蟆麽",mai:"买埋麦卖迈脉劢荬霾",man:"满瞒馒蛮蔓曼慢漫谩墁幔缦熳镘颟螨鳗鞔",mang:"芒茫盲氓忙莽邙漭硭蟒",mao:"贸猫茅锚毛矛铆卯茂冒帽貌袤茆峁泖瑁昴牦耄旄懋瞀蝥蟊髦",me:"么麽",mei:"没美每媒魅玫枚梅酶霉煤眉镁昧寐妹媚糜莓嵋猸浼湄楣镅鹛袂",men:"们门闷扪焖懑钔",meng:"盟萌蒙檬锰猛梦孟勐甍瞢懵朦礞虻蜢蠓艋艨",mi:"密眯醚靡糜迷谜弥米秘觅泌蜜幂芈谧咪嘧猕汨宓弭脒祢敉縻麋",mian:"面免棉眠绵冕勉娩缅沔渑湎腼眄",miao:"描苗瞄藐秒渺庙妙喵邈缈缪杪淼眇鹋",mie:"蔑灭乜咩蠛篾",min:"民抿皿敏悯闽苠岷闵泯缗玟珉愍黾鳘",ming:"明名命螟鸣铭冥茗溟暝瞑酩",miu:"谬缪",mo:"没模脉摸摹蘑膜磨摩魔抹末莫墨默沫漠寞陌谟茉蓦馍嫫嬷殁镆秣瘼耱貊貘麽",mou:"谋牟某侔哞缪眸蛑鍪",mu:"目母牟拇牡亩姆墓暮幕募慕木睦牧穆仫坶苜沐毪钼",na:"那拿哪呐钠娜纳讷捺肭镎衲",nai:"氖乃奶耐奈鼐佴艿萘柰",nan:"男南难喃囝囡楠腩蝻赧",nang:"囊攮囔馕曩",nao:"脑挠恼闹淖孬垴呶猱瑙硇铙蛲",ne:"呢讷",nei:"内馁",nen:"嫩恁",neng:"能",ni:"你妮霓倪泥尼拟匿腻逆溺伲坭蘼猊怩昵旎睨铌鲵",nian:"年蔫拈碾撵捻念粘廿埝辇黏鲇鲶",niang:"娘酿",niao:"鸟尿茑嬲脲袅",nie:"捏聂孽啮镊镍涅乜陧蘖嗫颞臬蹑",nin:"您",ning:"柠狞凝宁拧泞佞咛甯聍",niu:"牛扭钮纽拗狃忸妞",nong:"农脓浓弄侬哝",nu:"努奴怒弩胬孥驽",nv:"女恧钕衄",nuan:"暖",nue:"虐疟挪",nuo:"娜懦糯诺傩搦喏锘",o:"哦噢",ou:"欧鸥殴藕呕偶沤讴怄瓯耦",pa:"扒耙啪趴爬帕怕琶葩杷筢",pai:"牌派排拍徘湃俳蒎哌",pan:"攀潘盘磐盼畔判叛胖拚爿泮袢襻蟠蹒",pang:"磅乓庞旁耪胖彷夂滂逄攵螃",pao:"抛咆刨炮袍跑泡匏狍庖脬疱",pei:"培配呸胚裴赔陪佩沛辔帔旆锫醅霈",pen:"喷盆湓",peng:"朋砰抨烹澎彭蓬棚硼篷膨鹏捧碰堋嘭怦蟛",pi:"否被辟坯砒霹批披劈琵毗啤脾疲皮匹痞僻屁譬丕仳陴邳郫圮埤鼙芘擗噼庀淠媲纰枇甓罴铍癖疋蚍蜱貔",pian:"片便篇偏骗谝骈犏胼翩蹁",piao:"漂飘瓢票剽莩嘌嫖缥殍瞟螵",pie:"撇瞥丿苤彡氕",pin:"品频聘拼贫姘嫔榀牝颦",ping:"评平冯乒坪苹萍凭瓶屏俜娉枰鲆",po:"坡泼颇婆破魄迫粕叵鄱珀钋钷皤笸",pu:"普暴扑铺仆莆葡菩蒲埔朴圃浦谱曝瀑匍噗溥濮璞氆镤镨蹼",qi:"其企起期汽器启气奇缉欺栖戚妻七凄漆柒沏棋歧畦崎脐齐旗祈祁骑岂乞契砌迄弃泣讫亓俟圻芑芪萁萋葺蕲嘁屺岐汔淇骐绮琪琦杞桤槭耆祺憩碛颀蛴蜞綦鳍麒",qia:"夹掐恰洽葜袷髂",qian:"前钱牵扦钎铅千迁签仟谦乾黔钳潜遣浅谴堑嵌欠歉纤倩佥阡芊芡茜荨掮岍悭慊骞搴褰缱椠肷愆钤虔箝",qiang:"强将枪呛腔羌墙蔷抢戕嫱樯戗炝锖锵镪襁蜣羟跄",qiao:"壳橇锹敲悄桥瞧乔侨巧鞘撬翘峭俏窍削劁诮谯荞峤愀憔缲樵硗跷鞒",qie:"且切茄怯窃郄惬慊妾挈锲箧趄",qin:"亲钦侵秦琴勤芹擒禽寝沁芩揿吣嗪噙溱檎锓矜覃螓衾",qing:"情请庆轻青氢倾卿清擎晴氰顷苘圊檠磬蜻罄綮謦鲭黥",qiong:"琼穷邛茕穹蛩筇跫銎",qiu:"求球秋丘邱囚酋泅俅巯犰湫逑遒楸赇虬蚯蝤裘糗鳅鼽",qu:"区去取曲趋蛆躯屈驱渠娶龋趣诎劬苣蕖蘧岖衢阒璩觑氍朐祛磲鸲癯蛐蠼麴瞿黢",quan:"全权圈颧醛泉痊拳犬券劝诠荃犭悛绻辁畎铨蜷筌鬈",que:"确缺炔瘸却鹊榷雀阕阙悫",qun:"裙群逡麇",ran:"然燃冉染苒蚺髯",rang:"让瓤壤攘嚷禳穰",rao:"饶扰绕荛娆桡",re:"惹热喏",ren:"人任认壬仁忍韧刃妊纫仞荏饪轫稔衽",reng:"扔仍",ri:"日",rong:"容戎茸蓉荣融熔溶绒冗嵘狨榕肜蝾",rou:"揉柔肉糅蹂鞣",ru:"如入茹蠕儒孺辱乳汝褥蓐薷嚅洳溽濡缛铷襦颥",ruan:"软阮朊",rui:"蕊瑞锐芮蕤枘睿蚋",run:"闰润",ruo:"若弱偌箬",sa:"撒洒萨卅挲脎飒",sai:"赛腮鳃塞噻",san:"三叁伞散仨彡馓毵糁",sang:"桑嗓丧搡磉颡",sao:"搔骚扫嫂埽缫臊瘙鳋",se:"色塞瑟涩啬铯穑",sen:"森",seng:"僧",sha:"莎砂杀刹沙纱傻啥煞厦唼挲歃铩痧裟霎鲨",shai:"筛晒酾",shan:"删山珊苫杉煽衫闪陕擅赡膳善汕扇缮栅剡讪鄯埏芟潸姗嬗骟膻钐疝蟮舢跚鳝",shang:"上商尚赏墒伤晌裳垧泷绱殇熵觞",shao:"少绍鞘梢捎稍烧芍勺韶哨邵劭苕潲杓蛸筲艄",she:"设社奢赊蛇舌舍赦摄射慑涉厍佘揲猞滠歙畲麝",shen:"什身参深神甚申砷呻伸娠绅沈审婶肾慎渗诜谂莘葚哂渖椹胂矧蜃糁",sheng:"生声乘甥牲升绳省盛剩胜圣嵊渑晟眚笙",shi:"是时式什市实使事示始世施识视试师史十食势释失室匙狮湿诗尸虱石拾蚀矢屎驶士柿拭誓逝嗜噬适仕侍饰氏恃嘘谥埘莳蓍弑轼贳炻礻铈螫舐筮豕鲥鲺",shou:"手首受授收售守寿瘦兽狩绶艏",shu:"数术束输属殊述蔬枢梳抒叔舒淑疏书赎孰熟薯暑曙署蜀黍鼠树戍竖墅庶漱恕俞丨倏塾菽摅沭澍姝纾毹腧殳秫",shua:"刷耍唰",shuai:"率摔衰甩帅蟀",shuan:"栓拴闩涮",shuang:"霜双爽孀",shui:"说水谁睡税",shun:"吮瞬顺舜",shuo:"数说硕朔烁蒴搠妁槊铄",si:"司思似食四斯撕嘶私丝死肆寺嗣伺饲巳厮俟兕厶咝汜泗澌姒驷缌祀锶鸶耜蛳笥",song:"松送耸怂颂宋讼诵凇菘崧嵩忪悚淞竦",sou:"搜艘擞嗽叟薮嗖嗾馊溲飕瞍锼螋",su:"速诉苏素酥俗粟僳塑溯宿肃缩夙谡蔌嗉愫涑簌觫稣",suan:"算酸蒜狻",sui:"虽随隋绥髓碎岁穗遂隧祟谇荽濉邃燧眭睢",sun:"孙损笋荪狲飧榫隼",suo:"所索莎蓑梭唆缩琐锁唢嗦嗍娑桫挲睃羧",ta:"他它她塌塔獭挞蹋踏嗒闼溻漯遢榻沓*屏蔽的关键字*趿鳎",tai:"大态台胎苔抬泰酞太汰邰薹骀肽炱钛跆鲐",tan:"坛弹坍摊贪瘫滩檀痰潭谭谈坦毯袒碳探叹炭郯澹昙忐钽锬覃",tang:"汤塘搪堂棠膛唐糖倘躺淌趟烫傥帑惝溏瑭樘铴镗耥螗螳羰醣",tao:"讨掏涛滔绦萄桃逃淘陶套鼗叨啕洮韬饕",te:"特忒忑慝铽",teng:"藤腾疼誊滕",ti:"题提体梯剔踢锑蹄啼替嚏惕涕剃屉倜荑悌逖绨缇鹈裼醍",tian:"天添填田甜恬舔腆掭忝阗殄畋",tiao:"调条挑迢眺跳佻苕祧窕蜩笤粜龆鲦髫",tie:"帖贴铁萜餮",ting:"庭听厅烃汀廷停亭挺艇莛葶婷梃铤蜓霆",tong:"同统通桐酮瞳铜彤童桶捅筒痛佟仝茼嗵恸潼砼",tou:"投头偷透骰",tu:"图突凸秃徒途涂屠土吐兔堍荼菟钍酴",tuan:"团湍抟彖疃",tui:"推颓腿蜕褪退忒煺",tun:"囤褪吞屯臀氽饨暾豚",tuo:"拖托脱鸵陀驮驼椭妥拓唾乇佗坨庹沱柝橐砣箨酡跎鼍",wa:"挖哇蛙洼娃瓦袜佤娲腽",wai:"外歪崴",wan:"完湾万晚玩蔓豌弯顽丸烷碗挽皖惋宛婉腕剜芄莞菀纨绾琬脘畹蜿",wang:"网望汪王亡枉往旺忘妄罔惘辋魍",wei:"为位威未围维委巍微危韦违桅唯惟潍苇萎伟伪尾纬蔚味畏胃喂魏渭谓尉慰卫偎诿隈隗圩葳薇帏帷崴嵬猥猬闱沩洧涠逶娓玮韪軎炜煨痿艉鲔",wen:"文问闻稳瘟温蚊纹吻紊刎夂阌汶璺攵雯",weng:"嗡翁瓮蓊蕹",wo:"我挝蜗涡窝斡卧握沃倭莴喔幄渥肟硪龌",wu:"务无误物午恶巫呜钨乌污诬屋芜梧吾吴毋武五捂舞伍侮坞戊雾晤勿悟兀仵阢邬圬芴唔庑怃忤寤迕妩婺骛杌牾焐鹉鹜痦蜈鋈鼯",xi:"系息戏习希细西喜析栖昔熙硒矽晰嘻吸锡牺稀悉膝夕惜熄烯溪汐犀檄袭席媳铣洗隙僖兮隰郗茜菥葸蓰奚唏徙饩阋浠淅屣嬉玺樨曦觋欷歙熹禊禧皙穸裼蜥螅蟋舄舾羲粞翕醯蹊鼷",xia:"下瞎虾匣霞辖暇峡侠狭厦夏吓呷狎遐瑕柙硖罅黠",xian:"现限线显先见献衔险铣掀锨仙鲜纤咸贤舷闲涎弦嫌县腺馅羡宪陷冼苋莶藓岘猃暹娴氙燹祆鹇痫蚬筅籼酰跣跹霰",xiang:"相想项享详象响香像向降厢镶箱襄湘乡翔祥巷橡芗葙饷庠骧缃蟓鲞飨",xiao:"小销消效校萧硝霄削哮嚣宵淆晓孝肖啸笑哓崤潇逍骁绡枭枵蛸筱箫魈",xie:"些谢械楔歇蝎鞋协挟携邪斜胁谐写卸蟹懈泄泻屑偕亵勰燮薤撷獬廨渫瀣邂绁缬榭榍颉蹀躞",xin:"信新心欣薪芯锌辛忻衅囟馨莘昕歆镡鑫",xing:"行形型性幸姓省星腥猩惺兴刑邢醒杏陉荇荥擤饧悻硎",xiong:"兄凶胸匈汹雄熊芎",xiu:"修秀臭休羞朽嗅锈袖绣咻岫馐庥溴鸺貅髹",xu:"需许须序续墟戌虚嘘徐蓄酗叙旭畜恤絮婿绪吁诩勖圩蓿洫溆顼栩肷煦盱胥糈醑",xuan:"选宣轩喧悬旋玄癣眩绚儇谖萱揎泫渲漩璇楦暄炫煊碹铉镟痃",xue:"学削靴薛穴雪血谑噱泶踅鳕",xun:"询训讯迅寻浚勋熏循旬驯巡殉汛逊巽郇埙荀荨蕈薰峋徇獯恂洵浔曛窨醺鲟",ya:"压押鸦鸭呀丫芽牙蚜崖衙涯雅哑亚讶轧伢垭揠岈迓娅琊桠氩砑睚痖",yan:"言研验眼严颜焉咽阉烟淹盐蜒岩延阎炎沿奄掩衍演艳堰燕厌砚雁唁彦焰宴谚殷厣赝剡俨偃兖谳郾鄢埏芫菸崦恹闫阏湮滟妍嫣琰檐晏胭腌焱罨筵酽趼魇餍鼹",yang:"样央阳养殃鸯秧杨扬佯疡羊洋氧仰痒漾徉怏泱炀烊恙蛘鞅",yao:"要邀腰妖瑶摇尧遥窑谣姚咬舀药耀钥夭爻吆崾徭幺珧杳轺曜肴铫鹞窈繇鳐麽",ye:"业也页椰噎耶爷野冶掖叶曳腋夜液拽靥谒邺揶揲晔烨铘",yi:"一以已意易议医移艺益义艾蛇壹揖铱依伊衣颐夷遗仪胰疑沂宜姨彝椅蚁倚乙矣抑邑屹亿役臆逸肄疫亦裔毅忆溢诣谊译异翼翌绎刈劓佚佾诒圯埸懿苡荑薏弈奕挹弋呓咦咿嗌噫峄嶷猗饴怿怡悒漪迤驿缢殪轶贻欹旖熠眙钇镒镱痍瘗癔翊蜴舣羿翳酏黟",yin:"因音引银茵荫殷阴姻吟淫寅饮尹隐印胤鄞垠堙茚吲喑狺夤洇氤铟瘾窨蚓霪龈",ying:"影应营英迎樱婴鹰缨莹萤荧蝇赢盈颖硬映嬴郢茔荥莺萦蓥撄嘤膺滢潆瀛瑛璎楹媵鹦瘿颍罂",yo:"哟唷",yong:"用拥永佣臃痈庸雍踊蛹咏泳涌恿勇俑壅墉喁慵邕镛甬鳙饔",you:"有游由友优右邮幽悠忧尤铀犹油酉佑釉诱又幼卣攸侑莠莜莸尢呦囿宥柚猷牖铕疣蚰蚴蝣蝤繇鱿黝鼬",yu:"于育语域娱与遇尉迂淤盂榆虞愚舆余俞逾鱼愉渝渔隅予雨屿禹宇羽玉芋郁吁喻峪御愈欲狱誉浴寓裕预豫驭粥禺毓伛俣谀谕萸蓣揄圄圉嵛狳饫馀庾阈鬻妪妤纡瑜昱觎腴欤於煜熨燠聿畲钰鹆鹬瘐瘀窬窳蜮蝓竽臾舁雩龉",yuan:"员原源元院远鸳渊冤垣袁援辕园圆猿缘苑愿怨垸塬芫掾圜沅媛瑗橼爰眢鸢螈箢鼋",yue:"说乐阅越曰约跃钥岳粤月悦龠哕瀹栎樾刖钺",yun:"运耘云郧匀陨允蕴酝晕韵孕郓芸狁恽愠纭韫殒昀氲熨筠",za:"匝砸杂咋拶咂",zai:"在载子再栽哉灾宰崽甾",zan:"暂咱攒赞瓒昝簪糌趱錾",zang:"赃脏葬奘驵臧",zao:"造遭糟凿藻枣早澡蚤躁噪皂灶燥唣",ze:"责择则泽咋仄赜啧帻迮昃笮箦舴",zei:"贼",zen:"怎谮",zeng:"增综曾憎赠缯甑罾锃",zha:"扎喳渣札轧铡闸眨栅榨咋乍炸诈柞揸吒咤哳楂砟痄蚱齄",zhai:"翟摘斋宅窄债寨砦瘵",zhan:"站展战瞻毡詹粘沾盏斩辗崭蘸栈占湛绽谵搌旃",zhang:"章长张樟彰漳掌涨杖丈帐账仗胀瘴障仉鄣幛嶂獐嫜璋蟑",zhao:"着照找招朝昭沼赵罩兆肇召爪诏棹钊笊",zhe:"这者着浙遮折哲蛰辙锗蔗乇谪摺柘辄磔鹧褶蜇螫赭",zhen:"真圳珍斟甄砧臻贞针侦枕疹诊震振镇阵帧蓁浈缜桢椹榛轸赈胗朕祯畛稹鸩箴",zheng:"正政整证争蒸挣睁征狰怔拯症郑诤峥徵钲铮筝",zhi:"只之知制置址支直至织治质执职值致指志芝枝吱蜘肢脂汁植殖侄止趾旨纸挚掷帜峙智秩稚炙痔滞窒卮陟郅埴芷摭帙忮彘咫骘栉枳栀桎轵轾贽胝膣祉黹雉鸷痣蛭絷酯跖踬踯豸觯",zhong:"中种重终盅忠钟衷肿仲众冢忪锺螽舯踵",zhou:"州舟周洲诌粥轴肘帚咒皱宙昼骤荮啁妯纣绉胄碡籀繇酎",zhu:"注主助筑属珠株蛛朱猪诸诛逐竹烛煮拄瞩嘱著柱蛀贮铸住祝驻伫侏邾苎茱洙渚潴杼槠橥炷铢疰瘃竺箸舳翥躅麈",zhua:"挝抓爪",zhuai:"拽",zhuan:"专传转砖撰赚篆啭馔颛",zhuang:"状装幢桩庄妆撞壮僮",zhui:"椎锥追赘坠缀萑惴骓缒隹",zhun:"准屯谆肫窀",zhuo:"着捉拙卓桌琢茁酌啄灼浊倬诼擢浞涿濯禚斫镯",zi:"自资子字咨兹姿滋淄孜紫仔籽滓渍谘茈呲嵫姊孳缁梓辎赀恣眦锱秭耔笫粢趑觜訾龇鲻髭",zong:"综总鬃棕踪宗纵偬腙粽",zou:"邹走奏揍诹陬鄹驺鲰",zu:"组足租卒族祖诅阻俎菹镞",zuan:"钻纂攥缵躜",zui:"最嘴醉罪蕞觜",zun:"尊遵撙樽鳟",zuo:"作左昨佐柞做坐座阼唑嘬怍胙祚笮酢"};
})(window);
/**
 * 评级插件
 * @param  {Key-value}   [options]  配置
 * @param  {Number}      [options.MaxStar=5]       最大星级
 * @param  {Number}      [options.StarWidth=30]    每级所占宽度，与上面参数共同构成总宽度
 * @param  {Number}      [options.CurrentStar=0]   当级星级
 * @param  {Boolean}     [options.Enabled=true]    是否可用，当为false时，不能选择星级
 * @param  {Number}     [options.Half=0]          当Half为true时，可以选择每级的1/2处
 * @param  {Number}     [options.prefix=0]
 * @param  {Function}    callback                  回调
 * @return {Jquery}            jq对象
 */
$.fn.studyplay_star = function(options, callback) {
	//默认设置
	var settings = {
		MaxStar: 11,
		StarWidth: 10,
		CurrentStar: 0,
		Enabled: true,
		Half: 0,
		prefix: 0,
		mark: 0
	};

	var container = jQuery(this),
		_value =  container.data("value");
	if(_value) {
		settings.CurrentStar = _value; 
	}

	if (options) {
		jQuery.extend(settings, options);
	};
	container.css({
		"position": "relative",
		"float": "right"
	})
	.html('<ul class="studyplay_starBg"></ul>')
	.find('.studyplay_starBg').width(settings.MaxStar * settings.StarWidth)
	.html('<li class="studyplay_starovering" style="width:' + (settings.CurrentStar + 1) * settings.StarWidth + 'px; z-index:0;" id="studyplay_current"></li>');
	
	if (settings.Enabled) {
		var ListArray = "";
		if (settings.Half == 0) {
			for (k = 1; k < settings.MaxStar + 1; k++) {
				ListArray += '<li class="studyplay_starON" style="width:' + settings.StarWidth * k + 'px;z-index:' + (settings.MaxStar - k + 1) + ';"></li>';
			}
		}
		if (settings.Half == 1) {
			for (k = 1; k < settings.MaxStar * 2 + 1; k++) {
				ListArray += '<li class="studyplay_starON" style="width:' + settings.StarWidth * k / 2 + 'px;z-index:' + (settings.MaxStar - k + 1) + ';"></li>';
			}
		}
		container.find('.studyplay_starBg').append(ListArray);

		container.find('.studyplay_starON').hover(function() {
				var studyplay_count = settings.MaxStar - $(this).css("z-index");
				$(this).siblings('.studyplay_starovering').hide();
				$('#processbar_info_' + settings.prefix).html('&nbsp;' + studyplay_count * 10 + '%');
				$(this).removeClass('studyplay_starON').addClass("studyplay_starovering");
				$("#studyplay_current" + settings.prefix).hide();
				container.trigger("star.enter", { count: studyplay_count, current: $(this) })
			},
			function() {
				var studyplay_count = settings.MaxStar - $(this).css("z-index");
				$(this).siblings('.studyplay_starovering').show();
				$('#processbar_info_' + settings.prefix).html('&nbsp;' + $('#processinput_' + settings.prefix).val() * 10 + '%');
				$(this).removeClass('studyplay_starovering').addClass("studyplay_starON");
				$("#studyplay_current" + settings.prefix).show();
				container.trigger("star.leave", { count: studyplay_count, current: $(this) })		
			})
			.click(function() {
				var studyplay_count = settings.MaxStar - $(this).css("z-index");
				$(this).siblings('.studyplay_starovering').width((studyplay_count + 1) * settings.StarWidth)
				if (settings.Half == 0)
					$("#studyplay_current" + settings.prefix).width('&nbsp;' + studyplay_count * settings.StarWidth)
				$(this).siblings('.studyplay_starovering').width('&nbsp;' + (studyplay_count + 1) * settings.StarWidth)
				if (settings.Half == 1)
					$("#studyplay_current" + settings.prefix).width((studyplay_count + 1) * settings.StarWidth / 2)
					//回调函数
				if (typeof callback == 'function') {
					if (settings.Half == 0)
						callback(studyplay_count, container);
					if (settings.Half == 1)
						callback(studyplay_count / 2, container);
					return;
				}
			})
	}
};
//$.fn.switch
//开关初始化
(function(){
	/**
	 * 初始化开关的类
	 * @class  Switch
	 * @param  {Element|Jquery} element 要初始化的元素，必须为input:checkbox元素
	 * @param  {Key-Value}      options [配置，目前未定义]
	 * @return {Object}         switch实例对象
	 */
	var Switch = function(element, options){
		this.$el = $(element);
		this.options = options;
		this.init();
	}
	Switch.prototype = {
		constructor: Switch,
		/**
		 * 初始化函数
		 * @method init
		 * @private
		 */
		init: function(){
			var $el = this.$el,
				cls = "toggle",
				isChecked = $el.prop('checked'),
				isDisabled = $el.prop('disabled');
			!isChecked && (cls += " toggle-off");
			isDisabled && (cls += " toggle-disabled");
			// $el.remove();
			this.toggle = $el.wrap('<label class="'+ cls +'"></label>').parent();
			// 将input的title属性赋予容器label
			this.toggle.attr('title', $el.attr('title'));
			if(!isDisabled){
				this._bindEvent();
			}
		},
		/**
		 * 事件绑定
		 * @method bindEvent
		 * @private
		 * @chainable
		 * @return {Object}        当前调用对象
		 */
		_bindEvent: function(){
			var that = this;
			this.$el.off("change.switch").on("change.switch", function(){
				if(!this.checked) {
					that.toggle.addClass("toggle-off");
				}else{
					that.toggle.removeClass("toggle-off");
				}
			})
			return this;
		},
		/**
		 * 事件解绑
		 * @method unbindEvent
		 * @private
		 * @chainable
		 * @return {Object}        当前调用对象
		 */
		_unbindEvent: function(){
			this.$el.off("change.switch");
			return this;
		},
		/**
		 * 打开开关
		 * @method turnOn
		 * @chainable
		 * @param  {Function} call 回调函数
		 * @return {Object}        当前调用对象
		 */
		turnOn: function(call){
			this.$el.prop("checked", true).trigger("change");
		},
		/**
		 * 关闭开关
		 * @method turnOff
		 * @chainable
		 * @param  {Function} call 回调函数
		 * @return {Object}        当前调用对象
		 */
		turnOff: function(){
			this.$el.prop("checked", false).trigger("change");
		},
		/**
		 * 禁用开关
		 * @method setDisabled
		 * @chainable
		 * @param  {Function} call 回调函数
		 * @return {Object}        当前调用对象
		 */
		setDisabled: function(call){
			this.toggle.addClass("toggle-disabled");
			this.$el.prop("disabled", true);
			this._unbindEvent()
			return this;
		},
		/**
		 * 启用开关
		 * @method setDisabled
		 * @chainable
		 * @param  {Function} call 回调函数
		 * @return {Object}        当前调用对象
		 */
		setEnabled: function(call){
			this.toggle.removeClass("toggle-disabled");
			this.$el.prop("disabled", false);
			this._bindEvent()
			return this;
		}
	}
	/**
	 * @class $.fn
	 */
	/**
	 * 初始化开关，类Switch的入口
	 * @method $.fn.iSwitch
	 * @param  {String|Object} option Switch方法名或配置（配置目前不可用
	 * @param  {Any}           [any]  传入Switch方法的参数，类型，长度不限
	 * @return {Jquery}        jq数组
	 */
	$.fn.iSwitch = function(option/*,...*/){
		var argu = Array.prototype.slice.call(arguments, 1);
		return this.each(function(){
			var data = $(this).data("switch");
			if(!data||!(data instanceof Switch)){
				$(this).data("switch", data = new Switch($(this), option));
			}
			if(typeof option === "string"){
				data[option] && data[option].apply(data, argu);
			}
		})
	}
	//全局调用
	$(function(){
		$('[data-toggle="switch"]').iSwitch();
	})
})();
