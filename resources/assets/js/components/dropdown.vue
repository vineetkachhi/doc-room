<template>
  <div @mouseover="handleMouseOver" @mouseleave="handleMouseLeave">
    <a>
      {{ title }}
    </a>
    <slot></slot>
  </div>
</template>

<script>
import $ from "jquery";
export default {
  props: {
    title: {
      type: String,
      default() {
        return "";
      },
    },
  },
  methods: {
    handleMouseOver: function (e) {
      const $dropdownMenu = $(e.currentTarget).find(".dropdown-menu");

      if ($dropdownMenu) {
        $($dropdownMenu).stop(true, true).delay(200).fadeIn(250);
      }
    },
    handleMouseLeave: function (e) {
      const $dropdownMenu = $(e.currentTarget).find(".dropdown-menu");

      if ($dropdownMenu) {
        $($dropdownMenu).stop(true, true).delay(200).fadeOut(250);
      }
    },
  },
};
</script>
