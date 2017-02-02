
var MUD = {};

function processTimeout() {
   document.getElementById("LastUpdateTime").innerHTML=get_now();
   setTimeout('processTimeout()', 1000);
   make_event_request();
}

MUD.previous_seq = 0;

function get_location() {
   return current_location;
} //TODO make a location helper class

function make_preliminary_requests() {
   make_room_info_request();
   make_char_info_request(char_id);
}

function make_event_request() {
   var parameters = encodeURI("etime=" + previous_request_time + "&eloc=" + get_location().toString());
   var request = ajax_request();
   request.onreadystatechange = event_request_callback
   request.open("GET", "events.php?"+parameters, true)
   request.send()
}

function event_request_callback() {
   if (this.readyState == 4) {
      if (this.status == 200) {
         if (this.responseXML != null) {
            process_events_xml(this.responseXML);
         }
      } else {
         alert("Event request ajax error (non-200 status code): " + this.status.asString() + " => " + this.statusText)
      }
   }
}

function ajax_request() {
   var request = new XMLHttpRequest();
   //TODO make a version of this that works with Internet Explorer
   return request
}

function process_events_xml(xml) {
   var events = xml.getElementsByTagName('event');
   for (j=0; j<events.length; j++) {
      var seq = make_int(events[j].getAttribute('seq'));
      var who = events[j].getAttribute('name');
      var action = events[j].getAttribute('action');
      var when = events[j].getAttribute('etime');
      if (when > previous_request_time) previous_request_time = when;
      var extra = events[j].childNodes[0].nodeValue;
      if (seq > MUD.previous_seq) {
         MUD.previous_seq = seq;
         if (action === "say") {
            insert_into_event_display("<br>" + who + " <b>said</b> " + extra);
         } else if (action === "appear") {
            insert_into_event_display("<br>" + who + " <b>appears</b> " + extra);
         } else {
            insert_into_event_display("<br>" + who + " <b>" + action + "</b> " + extra);
         }
         //if ((action === "enter") || (action === "exit") || (action === "vanish") || (action === "appear")) {
         if (room_may_be_updated(action)) {
            make_room_info_request();
         }
      }
   }
   var element = document.getElementById("main");
   element.scrollTop = element.scrollHeight;
}

function room_may_be_updated(action) {
   if (action === "say") {
      return false;
   }
   return true;
}

function insert_into_event_display(what) {
   var element = document.getElementById("main");
   element.innerHTML += what;
}

function send_say_event() {
   var x = document.forms["dothis"]["saying"].value;
   var non_empty_string = encodeURIComponent(x);
   if ("" != non_empty_string) {
      post_event("say", non_empty_string);
   }

   document.forms["dothis"]["saying"].value = "";
   return false;
}

function post_event(action, extra) {
   var parameters = encodeURI("char_id=" + char_id + "&eloc=" + get_location().toString() + "&etime=" + get_now() + "&action=" + action + "&extra=") + extra;
   put_debug("debug_1", parameters);
   var request = ajax_request();
   request.open("GET", "do.php?"+parameters, false)
   request.send()
}

function put_debug(where, what) {
   document.getElementById(where).innerHTML = what;
}

function make_room_info_request() {
   var parameters = encodeURI("id=" + get_location().toString());
   var request = ajax_request();
   request.onreadystatechange = accept_room_info_update
   request.open("GET", "get_room.php?"+parameters, false)
   request.send()
}

function accept_room_info_update() {
   if (this.readyState == 4) {
      if (this.status == 200) {
         if (this.responseXML != null) {
            process_room_info_xml(this.responseXML);
         }
      } else {
         var oops = "Room Info request ajax error (non-200 status code): " + this.status.asString() + " => " + this.statusText;
         alert(oops);
         put_debug("alert_1", oops);
      }
   }
}

MUD.current_portals = [];
current_people = [];
current_things = [];
function process_room_info_xml(xml) {

   MUD.clear_move_buttons();

   var portal_elements = xml.getElementsByTagName('portal');
   var p = [];
   var place_list = [];
   for (j=0; j<portal_elements.length; j++) {
      var id = make_int(portal_elements[j].getAttribute('id'));
      var state = make_int(portal_elements[j].getAttribute('state'));
      var dir = make_int(portal_elements[j].getAttribute('direction'));
      var destination = make_int(portal_elements[j].getAttribute('destination'));
      MUD.color_move_button(dir, state);
      var dest_name = portal_elements[j].getAttribute('dest_name');
      var entry = {"id":id, "state":state, "direction":dir, "destination":destination, "dest_name":dest_name};
      p.push(entry);
      place_list.push(MUD.dir_name(dir) + ":" + dest_name)
   }
   MUD.current_portals = p;
   echo_unordered_list("exits", place_list);

   var name_element = xml.getElementsByTagName('name');
   document.getElementById("room_name").innerHTML = name_element[0].childNodes[0].nodeValue;
   var desc_element = xml.getElementsByTagName('description');
   document.getElementById("room_desc").innerHTML = desc_element[0].childNodes[0].nodeValue;

   var room_entities = xml.getElementsByTagName('content');
   var people = [];
   var things = [];
   MUD.thing_in_room_lookup = [];
   for (j=0; j<room_entities.length; j++) {
      var id = make_int(room_entities[j].getAttribute('id'));
      if (char_id != id) {
         var type = make_int(room_entities[j].getAttribute('type'));
         var name = room_entities[j].getAttribute('name');
         if (0 == type) {
            people.push(name);
            put_debug("debug_2", "people name " + name);
         } else {
            things.push(name);
            MUD.thing_in_room_lookup[name] = id;
         }
      }
   }
   current_people = people;
   current_things = things;
   echo_unordered_list("people", people);
   echo_drag_drop_list("things", things);
}

function make_char_info_request(cid) {
   var parameters = encodeURI("id=" + cid.toString());
   var request = ajax_request();
   request.onreadystatechange = accept_char_info_update
   request.open("GET", "get_char.php?"+parameters, false)
   request.send()
}

function accept_char_info_update() {
   if (this.readyState == 4) {
      if (this.status == 200) {
         if (this.responseXML != null) {
            process_char_info_xml(this.responseXML);
         }
      } else {
         var oops = "Char Info request ajax error (non-200 status code): " + this.status.asString() + " => " + this.statusText;
         alert(oops);
         put_debug("alert_1", oops);
      }
   }
}

function process_char_info_xml(xml) {
   //clear_hands();
   MUD.thing_on_char_lookup = []
   var things = [];
   var contents = xml.getElementsByTagName('content');
   for (j=0; j<contents.length; j++) {
      var id = make_int(contents[j].getAttribute('id'));
      var type = make_int(contents[j].getAttribute('type'));
      var name = contents[j].getAttribute('name');
      console.log("char_info item " + id + " type " + type + " name " + name);
      things.push(name);
      MUD.thing_on_char_lookup[name] = id;
      //if (2 === type) {
      //   show_in_hand('left_hand', name);
      //} else if (3 === type) {
      //   show_in_hand('right_hand', name);
      //}
   }
   echo_drag_drop_list("my_stuff", things);
}


//function clear_hands() {
//   O('left_hand').innerHTML = "";
//   S('left_hand').background = "White";
//   O('right_hand').innerHTML = "";
//   S('right_hand').background = "White";
//}

//function show_in_hand(hand, what) {
//   O(hand).innerHTML = "<span class='grabable' draggable='true' ondragstart='drag_start(event)' id='" + what + "'>" + what + "</span>";
//}

function echo_unordered_list(where, the_list) {
   var list_div = document.getElementById(where);
   list_div.innerHTML = "<ul>";
   for (j=0; j<the_list.length; ++j) {
      list_div.innerHTML += ("<li>" + the_list[j] + "</li>");
   }
   list_div.innerHTML += "</ul>";
}

function echo_drag_drop_list(where, the_list) {
   var list_div = document.getElementById(where);
   //list_div.innerHTML = "<ul>";
   list_div.innerHTML = "";
   for (j=0; j<the_list.length; ++j) {
      list_div.innerHTML += ("<span class='grabable' draggable='true' ondragstart='drag_start(event)' id='" + the_list[j] + "'>" + the_list[j] + "</span>");
   }
   //list_div.innerHTML += "</ul>";
}

function make_int(something) {
   return parseInt(something, 10);
}

function move(direction) {
   var dir=make_int(direction);
   var found=false;
   var dest;
   var dest_name;
   for (var j=0; j<MUD.current_portals.length; ++j) {
      if (MUD.current_portals[j].direction == dir) {
         found=true;
         dest=MUD.current_portals[j].destination;
         dest_name=MUD.current_portals[j].dest_name;
         break;
      }
   }
   if (found) {
      put_debug("debug_3", "Direction " + direction + " leads to room " + dest_name);
      post_event("exit", char_name + " leaves the room through the " + MUD.dir_name(dir) + " exit.");
      insert_into_event_display("<br>" + char_name + " leaves the room through the " + MUD.dir_name(dir) + " exit."); // see yourself leave
      post_move_move(dest.toString());
      current_location=dest;
      insert_into_event_display("<hr>");
      post_event("enter", char_name + " enters the room from the " + MUD.opposite_dir_name(dir) + " entrance.");
      make_room_info_request();
   } else {
      put_debug("debug_3", "Direction " + direction + " is invalid.");
   }
}

MUD.direction_names = ["Down", "Up", "North", "NNE", "NE", "ENE", "East", "ESE", "SE", "SSE", "South", "SSW", "SW", "WSW", "West", "WNW", "NW", "NNW"];
MUD.dir_name = function (d) {
   return MUD.direction_names[d+2];;
}
MUD.opposite_dir_name = function (d) {
   if (d == -1) return MUD.dir_name(-2);
   if (d == -2) return MUD.dir_name(-1);
   return MUD.dir_name( (d+8) % 16);
}

MUD.clear_move_buttons = function () {
   var j = 0;
   for (j = 0; j < MUD.direction_names.length; ++j) {
      MUD.color_named_button(MUD.direction_names[j], "white");
   }
}
MUD.color_move_button = function (dir, state) {
   var direction_name = MUD.dir_name(dir);
   console.log("MUD.color_move_button dir=" + dir + " (" + direction_name + ") state=" + state);
   if (state < 2) {
      // 0 and 1 are open
      MUD.color_named_button(direction_name, "LawnGreen");
   } else {
      MUD.color_named_button(direction_name, "Tomato");
   }
}
MUD.color_named_button = function (button_id, color) {
   var button_element = document.getElementById(button_id);
   if (null !== button_element) {
      button_element.style.background = color;
      console.log(button_element);
   }
}

function process_key(e) {
   if (null == e) {
      e = window.event;
   }
   if (e.keyCode == 13) {
      send_say_event();
   }
}

function never_submit() {
   return false;
}

// http://stackoverflow.com/questions/155188/trigger-a-button-click-with-javascript-on-the-enter-key-in-a-text-box
// JavaScript to hijack "enter" in a text box
//document.getElementById("id_of_textbox")
//    .addEventListener("keyup", function(event) {
//    event.preventDefault();
//    if (event.keyCode == 13) {
//        document.getElementById("id_of_button").click();
//    }
//});



function stop_playing() {
   post_event("exit", char_name + " vanishes into thin air.");
   post_move_vanish();
   window.location.href='meta.php';
}

function post_move_vanish() {
   var parameters = encodeURI("id=" + char_id + "&action=exit");
   var request = ajax_request();
   request.open("GET", "move.php?"+parameters, false);
   request.send()
}

function post_move_move(destination) {
   var parameters = "id=" + char_id + "&action=move&destination=" + destination;
   put_debug("debug_4", parameters);
   var request = ajax_request();
   request.open("GET", "move.php?"+parameters, false);
   request.send()
}

function allow_room_drop(drag_event) {
   drag_event.preventDefault();
   put_debug("debug_2", drag_event.target.id);
}

function allow_char_drop(drag_event) {
   drag_event.preventDefault();
   put_debug("debug_2", drag_event.target.id);
}

function drop_to_char(drop_event) {
    drop_event.preventDefault();
    var data = drop_event.dataTransfer.getData("text");
    drop_event.target.appendChild(document.getElementById(data));
    take_item(data, drop_event.target.id);
}

function allow_hand_drop(drag_event) {
   console.log("allow_hand_drop drag_event.target.id=" + drag_event.target.id);
   put_debug("debug_1", drag_event.target.id);
   //if (document.getElementById(drag_event.target.id).innerHTML === "") {
      drag_event.preventDefault();
   //}
   S(drag_event.target.id).background = "Green"
}

function leave_hand(drag_event) {
   S(drag_event.target.id).background = "White"
}

function O(object_id) {
   if (typeof object_id === 'object') {
      return object_id;
   }
   return document.getElementById(object_id);
}

function S(object_id) {
   return O(object_id).style
}

function drag_start(drag_event) {
    drag_event.dataTransfer.setData("text", drag_event.target.id);
    drag_event.dataTransfer.dropEffect = "move";
}

function drop_to_hand(drop_event) {
    drop_event.preventDefault();
    var data = drop_event.dataTransfer.getData("text");
    drop_event.target.appendChild(document.getElementById(data));
    take_item(data, drop_event.target.id);
}

function drop_to_room(drop_event) { // drop into thing list
   drop_event.preventDefault();
   var data = drop_event.dataTransfer.getData("text");
   drop_event.target.appendChild(document.getElementById(data));
   put_item(data);
}

function put_item(item) {
   post_event("put", item);

   var parameters = "id=" + MUD.thing_on_char_lookup[item] + "&action=put&where=" + get_location();
   console.log("put_item(" + item + ") parameters " + parameters);
   //put_debug("debug_4", parameters);
   var request = ajax_request();
   request.open("GET", "move.php?"+parameters, false);
   request.send()

   make_char_info_request(char_id);
}

function take_item(item, hand) {
    post_event("take", item);

   var locator_kind = 2;
   if ("right_hand" === hand) {
      locator_kind = 3;
   }

   var parameters = "id=" + MUD.thing_in_room_lookup[item] + "&action=take&who=" + char_id + "&kind=" + locator_kind;
   console.log("take_item(" + item + ") parameters " + parameters);
   //put_debug("debug_4", parameters);
   var request = ajax_request();
   request.open("GET", "move.php?"+parameters, false);
   request.send()

   make_char_info_request(char_id);
}
