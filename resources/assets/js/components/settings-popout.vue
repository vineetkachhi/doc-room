<template>
  <div
    class="settings-popout popout"
    :class="{ active: active }"
    v-click-outside="handleOutsideClick"
  >
    <div class="button" @click="togglePopout">
      <i class="icon setting-icon fas fa-cog"></i>
    </div>
    <div class="options-section">
      <form @submit.prevent="handleSave">
        <div class="field">
          <label for="room-col-width">Room Column Width:</label>
          <input
            type="text"
            name="room-col-width"
            v-model="roomWidth"
            autocomplete="off"
          />
        </div>
        <div class="field">
          <label for="doctor-col-width">Doctor Column Width:</label>
          <input
            type="text"
            name="doctor-col-width"
            v-model="doctorWidth"
            autocomplete="off"
          />
        </div>
        <button class="submit-button default-btn" type="submit">Save</button>
      </form>
      <hr />
      <a href="/docroom/super"
        ><button
          class="super-button default-btn blue"
          type="button"
          v-if="userRole == 3"
        >
          Dashboard
        </button></a
      >
      <a href="/admin"
        ><button
          class="admin-button default-btn blue"
          type="button"
          v-if="userRole == 2"
        >
          Dashboard
        </button></a
      >
      <a href="/logout"
        ><button class="logout-button default-btn blue right" type="button">
          Logout
        </button></a
      >
    </div>
  </div>
</template>

<script>
import $ from "jquery";
import ClickOutside from "vue-click-outside";

export default {
  data: function () {
    return {
      loading: false,
      active: false,
      roomWidth: 150,
      doctorWidth: 300,
    };
  },
  props: {
    userId: {
      type: String,
      default: function () {
        return "";
      },
    },
    groupId: {
      type: String,
      default: function () {
        return "";
      },
    },
    userRole: {
      type: String,
      default: function () {
        return "";
      },
    },
  },
  methods: {
    togglePopout: function () {
      this.active = !this.active;
    },
    isNumeric: function (n) {
      return !isNaN(parseFloat(n)) && isFinite(n);
    },
    handleSave: function () {
      if (
        !this.isNumeric(this.roomWidth) ||
        !this.isNumeric(this.doctorWidth)
      ) {
        alert("Width must be a number.");
      }

      axios
        .post("/api/setWidth", {
          userId: this.userId,
          groupId: this.groupId,
          roomWidth: this.roomWidth,
          doctorWidth: this.doctorWidth,
        })
        .then((response) => {
          console.log(response);
        });
    },
    handleOutsideClick: function () {
      this.active = false;
    },
  },
  created: function () {
    if (roomWidth) {
      this.roomWidth = roomWidth;
    }

    if (doctorWidth) {
      this.doctorWidth = doctorWidth;
    }
  },
  directives: { ClickOutside },
};
</script>
