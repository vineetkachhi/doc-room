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
          v-bind:key="doctor.id"
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
          <span class="roomNameDetails">{{ room["name"] }}</span>
          <div class="timerDetails">
            <label
              :id="'minutes_' + room['id']"
              class="timer-minutes"
              :timer-minutes="room['timer_minutes']"
            ></label>
            <span v-show="seconds_display == 'true'">
              <label :id="'colon_' + room['id']" class="timer-colon"></label>
              <label
                :id="'seconds_' + room['id']"
                class="timer-seconds"
                :timer-seconds="room['timer_seconds']"
              ></label>
            </span>
          </div>
        </div>
      </draggable>
    </div>
  </div>
</template>

<script>
var intervalValues = [];
import draggable from "vuedraggable";
import countdown from "vuejs-countdown";

export default {
  components: {
    draggable,
    countdown,
  },
  data: function () {
    return {
      unassignedRooms: [],
      doctors: [],
      roomColumnWidth: 150,
      doctorColumnWidth: 300,
      seconds_display: process.env.MIX_SHOW_SECONDS,
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
      if (doctorId == 0) {
        clearInterval(intervalValues["interval_" + roomId]);
      }
      if (fromDoctorId == 0) {
        clearInterval(intervalValues["interval_" + roomId]);
        var totalSeconds = 0;
        setRoomTimerOn(roomId, totalSeconds);
        intervalValues["interval_" + roomId] = setInterval(function () {
          ++totalSeconds;
          setRoomTimerOn(roomId, totalSeconds);
        }, 1000);
      }
      axios
        .post("/docroom/api/move", {
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
        .post("/docroom/api/sort", {
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
        .post("/docroom/api/remove", {
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
        .post("/docroom/api/refresh", {
          groupId: this.groupId,
        })
        .then((response) => {
          $.each(response.data.doctors, function (doctorIndexs, doctorValues) {
            $.each(doctorValues.rooms, function (roomIndex, roomValues) {
              var timerMinute = roomValues.timer_minutes;
              var timerSeconds = roomValues.timer_seconds;
              var totalSeconds = timerMinute * 60 + parseInt(timerSeconds);
              var roomId = roomValues.id;
              clearInterval(intervalValues["interval_" + roomValues.id]);
              setRoomTimerOn(roomId, totalSeconds);
              intervalValues["interval_" + roomId] = setInterval(function () {
                ++totalSeconds;
                setRoomTimerOn(roomId, totalSeconds);
              }, 1000);
            });
          });
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

    var ably = new Ably.Realtime(process.env.MIX_ABLY_KEY);
    var channel = ably.channels.get(
      "door-room-channel_" + app_name + "_" + groupId
    );
    channel.subscribe("update", (res) => {
      this.refresh();
    });
  },
};

function setRoomTimerOn(roomId, totalSeconds) {
  var timerMinute = padWithZero(parseInt(totalSeconds / 60));
  var timerSeconds = padWithZero(totalSeconds % 60);
  if (timerMinute > 99) {
    var timerColor = "red";
    var timerMinute = "XX";
    var timerSeconds = "XX";
  } else if (timerMinute > 40) {
    var timerColor = "rgb(253, 144, 1)";
  } else {
    var timerColor = "green";
  }
  $("#minutes_" + roomId)
    .parent()
    .css("color", timerColor);
  if (document.getElementById("seconds_" + roomId))
    document.getElementById("seconds_" + roomId).innerHTML = timerSeconds;
  if (document.getElementById("minutes_" + roomId))
    document.getElementById("minutes_" + roomId).innerHTML = timerMinute;
  if (document.getElementById("colon_" + roomId))
    document.getElementById("colon_" + roomId).innerHTML = ":";
}

function padWithZero(formattedValues) {
  var valueString = formattedValues + "";
  if (valueString.length < 2) {
    return "0" + valueString;
  } else {
    return valueString;
  }
}
$(document).ready(function () {
  $(window).on("unload", function () {});
  $(".room").each(function (i, obj) {
    if ($(obj).attr("data-doctor-id") != 0) {
      var timerMinute = $(obj)
        .find(".timerDetails")
        .find(".timer-minutes")
        .attr("timer-minutes");
      var timerSeconds = $(obj)
        .find(".timerDetails")
        .find(".timer-seconds")
        .attr("timer-seconds");
      var totalSeconds = timerMinute * 60 + parseInt(timerSeconds);
      var roomId = $(obj).attr("data-room-id");
      intervalValues["interval_" + roomId] = setInterval(function () {
        ++totalSeconds;
        setRoomTimerOn(roomId, totalSeconds);
      }, 1000);
    }
  });
});
</script>
