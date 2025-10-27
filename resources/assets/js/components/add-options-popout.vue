<template>
  <div class="add-options-popout popout" :class="{active: active}" v-click-outside="handleOutsideClick">
    <div class="button" @click="togglePopout">
      <i class="icon add-icon fas fa-plus"></i>
    </div>
    <div class="options-section">
      <form @submit.prevent="handleSubmit">
        <div class="field">
          <label for="add-room">Add Room:</label>
          <input type="text" name="room" v-model="room" autocomplete="off" />
        </div>
        <div class="field">
          <label for="add-room">Add Doctor:</label>
          <input type="text" name="doctor" v-model="doctor" autocomplete="off" />
        </div>
        <button class="submit-button default-btn" type="submit" v-if="!loading">Add</button>
        <button class="submit-button default-btn" disabled v-else>Loading</button>
      </form>
    </div>
  </div>
</template>

<script>
  import ClickOutside from 'vue-click-outside';

  export default {
    data: function() {
      return {
        room: '',
        doctor: '',
        loading: false,
        active: false,
      }
    },
    props: {
      userId: {type: String, default: function() { return '' }},
      groupId: {type: String, default: function() { return '' }},
    },
    methods: {
      togglePopout: function() {
        this.active = !this.active;
      },
      handleSubmit: function() {
        if(!this.room && !this.doctor) {
          return false;
        }

        this.loading = true;
        axios.post('/docroom/api/add', {
          userId: this.userId,
          groupId: this.groupId,
          room: this.room,
          doctor: this.doctor,
        }).then((response) => {
          if(response.data.success) {
            this.room = '';
            this.doctor = '';
          }

          this.loading = false;
        });
      },
      handleOutsideClick: function() {
        this.active = false;
      }
    },
    directives: { ClickOutside }
  }
</script>
