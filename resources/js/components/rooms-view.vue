<template>
  <div class="rooms-container">
    <div class="doctors">
      <div class="room-col" :style="{ width: roomColumnWidth + 'px' }">
        &nbsp;
      </div>
      <div
        class="doctor-cols"
        v-if="doctors.length"
        :style="{ width: 'calc(100vw - ' + roomColumnWidth + 'px)' }"
      >
        <div
          class="doctor-col"
          :style="{ width: doctorColumnWidth + 'px' }"
          v-for="doctor in doctors"
          @mouseenter="handleCloseBtnShow"
          @mouseleave="handleCloseBtnHide"
        >
          <div
            :data-doctor-id="doctor['id']"
            class="close-btn"
            @click="handleRemoveDoctor"
          >
            <i class="fas fa-times"></i>
          </div>
          {{ doctor["name"] }}
        </div>
      </div>
    </div>
    <div class="rooms">
      <draggable
        :style="{ width: roomColumnWidth + 'px' }"
        v-model="unassignedRooms"
        data-doctor-id="0"
        class="room-col"
        :options="{ group: 'rooms' }"
        @add="handleDrop"
        @update="handleSort"
      >
        <div
          :data-doctor-id="0"
          :data-room-id="room['id']"
          class="room"
          v-for="room in unassignedRooms"
          :key="room['id']"
          @mouseenter="handleCloseBtnShow"
          @mouseleave="handleCloseBtnHide"
        >
          <div
            :data-room-id="room['id']"
            class="close-btn"
            @click="handleRemoveRoom"
          >
            <i class="fas fa-times"></i>
          </div>
          {{ room["name"] }}
        </div>
      </draggable>
      <draggable
        :style="{ width: doctorColumnWidth + 'px' }"
        v-model="doctor.rooms"
        :data-doctor-id="doctor['id']"
        class="doctor-col"
        v-for="doctor in doctors"
        :options="{ group: 'rooms' }"
        @add="handleDrop"
        @update="handleSort"
        :key="doctor['id']"
      >
        <div
          :data-doctor-id="doctor['id']"
          :data-room-id="room['id']"
          class="room"
          v-for="room in doctor.rooms"
          :key="room['id']"
          @mouseenter="handleCloseBtnShow"
          @mouseleave="handleCloseBtnHide"
        >
          <div
            :data-room-id="room['id']"
            class="close-btn"
            @click="handleRemoveRoom"
          >
            <i class="fas fa-times"></i>
          </div>
          {{ room["name"] }}
        </div>
      </draggable>
    </div>
  </div>
</template>

<script>
import draggable from "vuedraggable";

export default {
  components: {
    draggable,
  },
  data: function () {
    return {
      unassignedRooms: [],
      doctors: [],
      roomColumnWidth: 150,
      doctorColumnWidth: 300,
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
  },
  methods: {
    handleDrop: function (event) {
      const fromDoctorId = event.from.dataset.doctorId;
      const { roomId } = event.item.dataset;
      const { doctorId } = event.to.dataset;

      axios
        .post("/api/move", {
          groupId: this.groupId,
          roomId: roomId,
          from: fromDoctorId,
          doctorId: doctorId,
        })
        .then((response) => {
          console.log(response);
        });
    },
    handleSort: function (event) {
      console.log("handle sort");
      const { doctorId, roomId } = event.item.dataset;

      let rooms = [];
      if (doctorId == 0) {
        this.unassignedRooms.map((room, index) => {
          rooms.push({
            id: room.id,
            sort_index: index,
          });
        });
      } else {
        this.doctors.map((doctor) => {
          if (doctor.id == doctorId) {
            doctor.rooms.map((room, index) => {
              rooms.push({
                id: room.id,
                sort_index: index,
              });
            });
          }
        });
      }

      axios
        .post("/api/sort", {
          groupId: this.groupId,
          rooms: rooms,
        })
        .then((response) => {
          console.log(response);
        });
    },
    handleRemove: function (type, id) {
      console.log("handle remove");
      axios
        .post("/api/remove", {
          groupId: this.groupId,
          type: type,
          id: id,
        })
        .then((response) => {
          console.log(response);
        });
    },
    refresh: function () {
      axios
        .post("/api/refresh", {
          groupId: this.groupId,
        })
        .then((response) => {
          if (response.data.assignedRooms) {
            this.assignedRooms = response.data.assignedRooms;
          }
          if (response.data.unassignedRooms) {
            this.unassignedRooms = response.data.unassignedRooms;
          }
          if (response.data.doctors) {
            this.doctors = response.data.doctors;
          }
          if (response.data.roomWidth) {
            this.roomColumnWidth = response.data.roomWidth;
          }
          if (response.data.doctorWidth) {
            this.doctorColumnWidth = response.data.doctorWidth;
          }
        });
    },
    handleCloseBtnShow: function (e) {
      const $btn = $(e.target).find(".close-btn");

      $btn.addClass("active");
    },
    handleCloseBtnHide: function (e) {
      const $btn = $(e.target).find(".close-btn");

      $btn.removeClass("active");
    },
    handleRemoveRoom: function (e) {
      const { roomId } = e.currentTarget.dataset;

      if (confirm("Would you like to remove this room?")) {
        this.handleRemove("room", roomId);
      }
    },
    handleRemoveDoctor: function (e) {
      const { doctorId } = e.currentTarget.dataset;

      if (confirm("Would you like to remove this doctor?")) {
        this.handleRemove("doctor", doctorId);
      }
    },
  },
  created: function () {
    this.unassignedRooms = unassignedRooms;
    this.doctors = doctors;

    if (roomWidth) {
      this.roomColumnWidth = roomWidth;
    }

    if (doctorWidth) {
      this.doctorColumnWidth = doctorWidth;
    }

    var ably = new Ably.Realtime("FAiW1A.ARgEvg:4QRphJafdx7-hSGG");
    var channel = ably.channels.get(
      "door-room-channel_" + app_name + "_" + groupId
    );
    channel.subscribe("update", (res) => {
      this.refresh();
    });
  },
};
</script>
