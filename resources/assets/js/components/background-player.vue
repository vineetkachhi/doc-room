<template></template>

<script>
import $ from "jquery";
export default {
  props: {
    numOfImages: {
      type: Number,
      default: function () {
        return 1;
      },
    },
    time: {
      type: Number,
      default: function () {
        return 5000;
      },
    },
  },
  data: function () {
    return {
      path: "/docroom/img/global",
      prefix: "home-bg",
      type: "jpg",
      active: 1,
    };
  },
  mounted: function () {
    if (this.numOfImages === 1) {
      return;
    }

    const el = document.querySelector(".background");
    const placeholder = document.querySelector("#image-placeholder");

    setInterval(() => {
      this.active = this.active === this.numOfImages ? 1 : this.active + 1;

      $(placeholder).attr(
        "src",
        `${this.path}/${this.prefix}-${this.active}.${this.type}`
      );

      $(placeholder).one("load", () => {
        $(el)
          .stop()
          .fadeOut("slow", () => {
            $(el)
              .css(
                "background-image",
                `url('${this.path}/${this.prefix}-${this.active}.${this.type}')`
              )
              .fadeIn();
          });
      });
    }, this.time);
  },
};
</script>
