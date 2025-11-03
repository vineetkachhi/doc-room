<template>
  <div class="rooms-container">
    <div class="doctors">
      <div class="room-col" :style="{ width: roomColumnWidth + 'px' }">
        &nbsp;
      </div>

      <!-- 🩺 Draggable Doctor List -->
      <div
        class="doctor-cols"
        v-if="doctors.length"
        :style="{ width: 'calc(100vw - ' + roomColumnWidth + 'px)' }"
      >
        <draggable
          v-model="doctors"
          item-key="id"
          class="doctor-drag-wrapper"
          :style="{
            display: 'grid',
            gridTemplateColumns:
              'repeat(' + doctors.length + ', ' + doctorColumnWidth + 'px)',
            width: '100%',
          }"
          :group="{ name: 'doctors', pull: false, put: false }"
          @end="handleDoctorSort"
        >
          <template #item="{ element: doctor }">
            <div
              class="doctor-col doctor-col-header"
              :style="{ width: doctorColumnWidth + 'px' }"
              @mouseenter="handleCloseBtnShow"
              @mouseleave="handleCloseBtnHide"
            >
              <div class="doctor-header">
                <div class="left-buttons">
                  <div
                    class="edit-btn"
                    :data-doctor-id="doctor.id"
                    @click="openEditDoctor(doctor)"
                  >
                    <i class="fas fa-edit"></i>
                  </div>
                  <div
                    class="close-btn"
                    :data-doctor-id="doctor.id"
                    @click="handleRemoveDoctor"
                  >
                    <i class="fas fa-times"></i>
                  </div>
                </div>

                <div
                  class="doctor-name"
                  v-if="!(editingDoctor && editingDoctor.id === doctor.id)"
                >
                  {{ doctor.name }}
                </div>

                <div v-else>
                  <input
                    v-model="editingDoctor.name"
                    @keyup.enter="saveDoctorEdit"
                    @blur="saveDoctorEdit"
                    class="edit-input"
                    placeholder="Edit doctor name"
                    ref="editInput"
                  />
                </div>
              </div>
            </div>
          </template>
        </draggable>
      </div>
    </div>

    <!-- 🏠 Rooms Section -->
    <div class="rooms">
      <!-- Unassigned Rooms -->
      <draggable
        v-model="unassignedRooms"
        :item-key="'id'"
        class="room-col"
        :style="{ width: roomColumnWidth + 'px' }"
        :data-doctor-id="0"
        :group="{ name: 'rooms', pull: true, put: true }"
        @add="handleDrop"
        @update="handleSort"
      >
        <template #item="{ element }">
          <div
            class="room"
            :data-doctor-id="0"
            :data-room-id="element.id"
            @mouseenter="handleCloseBtnShow"
            @mouseleave="handleCloseBtnHide"
          >
            <div
              class="close-btn"
              :data-room-id="element.id"
              @click="handleRemoveRoom"
            >
              <i class="fas fa-times"></i>
            </div>
            {{ element.name }}
          </div>
        </template>
      </draggable>

      <!-- Doctor Assigned Rooms -->
      <div
        class="doctor-col"
        v-for="doctor in doctors"
        :key="doctor.id"
        :style="{ width: doctorColumnWidth + 'px' }"
        :data-doctor-id="doctor.id"
      >
        <draggable
          v-model="doctor.rooms"
          :item-key="'id'"
          :group="{ name: 'rooms', pull: true, put: true }"
          @add="handleDrop"
          @update="handleSort"
          :style="{ minHeight: '100%' }"
        >
          <template #item="{ element }">
            <div
              class="room"
              :data-doctor-id="doctor.id"
              :data-room-id="element.id"
              @mouseenter="handleCloseBtnShow"
              @mouseleave="handleCloseBtnHide"
            >
              <div
                class="close-btn"
                :data-room-id="element.id"
                @click="handleRemoveRoom"
              >
                <i class="fas fa-times"></i>
              </div>
              <span class="roomNameDetails">{{ element.name }}</span>
              <div class="timerDetails">
                <label
                  :id="'minutes_' + element.id"
                  class="timer-minutes"
                  :timer-minutes="element.timer_minutes"
                ></label>
                <span v-show="seconds_display === 'true'">
                  <label
                    :id="'colon_' + element.id"
                    class="timer-colon"
                  ></label>
                  <label
                    :id="'seconds_' + element.id"
                    class="timer-seconds"
                    :timer-seconds="element.timer_seconds"
                  ></label>
                </span>
              </div>
            </div>
          </template>
        </draggable>
      </div>
    </div>
  </div>
</template>

<script>
import $ from "jquery";
import draggable from "vuedraggable";
import countdown from "vuejs-countdown";
import axios from "axios";
import Ably from "ably";

window.$ = window.jQuery = $;

let intervalValues = [];

export default {
  components: { draggable, countdown },
  props: {
    userId: { type: String, default: "" },
    groupId: { type: String, default: "" },
  },
  data() {
    return {
      unassignedRooms: [],
      doctors: [],
      roomColumnWidth: 150,
      doctorColumnWidth: 300,
      seconds_display: process.env.MIX_SHOW_SECONDS,
      editingDoctor: null,
    };
  },
  methods: {
    handleDrop(event) {
      const roomId = event.item.dataset.roomId;
      const fromDoctorId = event.item.dataset.doctorId;
      const toDoctorId = event.to.closest(".doctor-col")?.dataset.doctorId || 0;

      if (toDoctorId == 0) clearInterval(intervalValues["interval_" + roomId]);
      if (fromDoctorId == 0) {
        clearInterval(intervalValues["interval_" + roomId]);
        let totalSeconds = 0;
        setRoomTimerOn(roomId, totalSeconds);
        intervalValues["interval_" + roomId] = setInterval(() => {
          ++totalSeconds;
          setRoomTimerOn(roomId, totalSeconds);
        }, 1000);
      }

      axios.post("/api/move", {
        groupId: this.groupId,
        roomId,
        from: fromDoctorId,
        doctorId: toDoctorId,
      });
    },

    handleSort(event) {
      const doctorId = event.from.closest(".doctor-col")?.dataset.doctorId || 0;
      let rooms = [];

      if (doctorId == 0) {
        this.unassignedRooms.forEach((room, index) => {
          rooms.push({ id: room.id, sort_index: index });
        });
      } else {
        const doctor = this.doctors.find((d) => d.id == doctorId);
        if (doctor && doctor.rooms)
          doctor.rooms.forEach((room, index) =>
            rooms.push({ id: room.id, sort_index: index })
          );
      }

      axios.post("/api/sort", { groupId: this.groupId, rooms });
    },

    handleDoctorSort() {
      const sortedDoctors = this.doctors.map((doctor, index) => ({
        id: doctor.id,
        sort_index: index,
      }));
      axios.post("/api/sort-doctors", {
        groupId: this.groupId,
        doctors: sortedDoctors,
      });
    },

    handleRemove(type, id) {
      axios.post("/api/remove", { groupId: this.groupId, type, id });
    },

    refresh() {
      axios.post("/api/refresh", { groupId: this.groupId }).then((response) => {
        const data = response.data;
        this.unassignedRooms = data.unassignedRooms || [];
        this.doctors = data.doctors || [];
        if (data.roomWidth) this.roomColumnWidth = data.roomWidth;
        if (data.doctorWidth) this.doctorColumnWidth = data.doctorWidth;

        this.doctors.forEach((doctor) => {
          doctor.rooms.forEach((room) => {
            let totalSeconds =
              room.timer_minutes * 60 + parseInt(room.timer_seconds);
            const roomId = room.id;
            clearInterval(intervalValues["interval_" + roomId]);
            intervalValues["interval_" + roomId] = setInterval(() => {
              ++totalSeconds;
              setRoomTimerOn(roomId, totalSeconds);
            }, 1000);
          });
        });
      });
    },

    handleCloseBtnShow(e) {
      $(e.currentTarget).find(".close-btn").addClass("active");
    },
    handleCloseBtnHide(e) {
      $(e.currentTarget).find(".close-btn").removeClass("active");
    },

    handleRemoveRoom(e) {
      const { roomId } = e.currentTarget.dataset;
      if (confirm("Would you like to remove this room?"))
        this.handleRemove("room", roomId);
    },
    handleRemoveDoctor(e) {
      const { doctorId } = e.currentTarget.dataset;
      if (confirm("Would you like to remove this doctor?"))
        this.handleRemove("doctor", doctorId);
    },

    openEditDoctor(doctor) {
      this.editingDoctor = { ...doctor };
      this.$nextTick(() => {
        const input = this.$refs.editInput;
        if (input && input.focus) input.focus();
      });
    },

    saveDoctorEdit() {
      if (!this.editingDoctor) return;
      const updatedDoctor = this.editingDoctor;

      axios
        .post("/api/update-doctor", {
          id: updatedDoctor.id,
          name: updatedDoctor.name,
          groupId: this.groupId,
        })
        .then(() => {
          const index = this.doctors.findIndex(
            (d) => d.id === updatedDoctor.id
          );
          if (index !== -1) {
            this.$set(this.doctors, index, {
              ...this.doctors[index],
              name: updatedDoctor.name,
            });
          }
        })
        .catch(() => console.log("Error updating doctor"))
        .finally(() => {
          this.editingDoctor = null; // <- input yahan close ho jayega
        });
    },
  },

  created() {
    this.unassignedRooms = window.unassignedRooms || [];
    this.doctors = window.doctors || [];
    if (window.roomWidth) this.roomColumnWidth = window.roomWidth;
    if (window.doctorWidth) this.doctorColumnWidth = window.doctorWidth;

    this.$nextTick(() => {
      this.doctors.forEach((doctor) => {
        doctor.rooms.forEach((room) => {
          let totalSeconds =
            room.timer_minutes * 60 + parseInt(room.timer_seconds);
          const roomId = room.id;
          setRoomTimerOn(roomId, totalSeconds);
          clearInterval(intervalValues["interval_" + roomId]);
          intervalValues["interval_" + roomId] = setInterval(() => {
            ++totalSeconds;
            setRoomTimerOn(roomId, totalSeconds);
          }, 1000);
        });
      });
    });

    var ably = new Ably.Realtime(process.env.MIX_ABLY_KEY);
    var channel = ably.channels.get(
      "door-room-channel_" + app_name + "_" + this.groupId
    );

    channel.subscribe("update", () => this.refresh());
  },
};

function setRoomTimerOn(roomId, totalSeconds) {
  let timerMinute = padWithZero(parseInt(totalSeconds / 60));
  let timerSeconds = padWithZero(totalSeconds % 60);
  let timerColor = "green";

  if (timerMinute > 99) {
    timerColor = "red";
    timerMinute = "XX";
    timerSeconds = "XX";
  } else if (timerMinute > 40) {
    timerColor = "rgb(253, 144, 1)";
  }

  const $el = $("#minutes_" + roomId).parent();
  $el.css("color", timerColor);
  $("#minutes_" + roomId).text(timerMinute);
  $("#seconds_" + roomId).text(timerSeconds);
  $("#colon_" + roomId).text(":");
}

function padWithZero(value) {
  return value.toString().padStart(2, "0");
}
</script>

<style scoped>
.doctor-header {
  display: flex;
  align-items: center;
  gap: 8px;
}

.left-buttons {
  display: flex;
  align-items: center;
  gap: 4px;
}

.edit-btn i {
  color: #007bff;
  cursor: pointer;
  font-size: 16px;
}

.close-btn i {
  color: red;
  cursor: pointer;
}

.edit-input {
  width: 90%;
  border: 1px solid #ccc;
  border-radius: 4px;
  padding: 2px 6px;
  margin-top: 4px;
}

.doctor-drag-wrapper {
  display: grid !important;
  align-items: stretch;
  justify-content: start;
}

.doctor-col-header {
  box-sizing: border-box;
  min-width: 0;
}

.left-buttons {
  display: flex;
  align-items: center;
  gap: 4px;
}

.edit-btn,
.close-btn {
  opacity: 0; /* default hide */
  visibility: hidden; /* default hide */
  transition: opacity 0.2s ease;
}

.doctor-col-header:hover .edit-btn,
.doctor-col-header:hover .close-btn,
.doctor-col-header .edit-btn.active,
.doctor-col-header .close-btn.active {
  opacity: 1; /* show on hover */
  visibility: visible;
}
</style>
