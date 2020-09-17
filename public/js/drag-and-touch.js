// This uses the touch API instead of drag and drop on a mobile device. After a 'mover' element is dragged
// into a 'receptacle' element, listeners are removed for the mover and receptacle so the receptacle won't
// receive any more movers and the mover can't be moved again.

// @see: https://github.com/deepakkadarivel/DnDWithTouch/blob/master/index.html
//
// Need to know:
//    All receptacle elements needs a class of 'receptacle'. Adds 'unlocked' class to all receptacle elements.
//    All draggable elements need a class of 'mover'
//    All movers need to be wrapped in a div with a 'mover__wrapper' class
// Synopsis:
//    Adds listeners to receptacles and movers. Also adds draggable attribute to movers.
let activeEvent = '';
let receiverIds = [];

function handleDragEnter(e) {
  // document.getElementById('app_status').innerHTML = "You are dragging over the " + e.target.getAttribute('id');
}

function handleDragLeave(e) {
}

function handleDragOver(e) {
  e.preventDefault();
}

function handleDrop(e) {
  e.preventDefault();
  const receiver = e.target;
  let element_id = e.dataTransfer.getData("text");
  const mover = document.getElementById(element_id);
  receiver.innerText = '';
  receiver.appendChild(mover);
  mover.removeAttribute("draggable")
  mover.style.cursor = "default";

  receiver.removeEventListener('dragenter', handleDragEnter, false);
  receiver.removeEventListener('dragover', handleDragOver, false);
  receiver.removeEventListener('dragleave', handleDragLeave, false);
  receiver.removeEventListener('drop', handleDrop, false);
}


function handleDragStart(e) {
  e.dataTransfer.dropEffect = "move";
  e.dataTransfer.setData("text", e.target.getAttribute('id'));
}

function handleDragEnd(e) {

}

function handleTouchStart(e) {
  activeEvent = 'start';
}


function handleTouchMove(e) {
  let touchLocation = e.targetTouches[0];
  let pageX = (touchLocation.pageX - 50) + "px";
  let pageY = (touchLocation.pageY - 50) + "px";
  e.target.style.position = "fixed";
  e.target.style.left = pageX;
  e.target.style.top = pageY;
  activeEvent = 'move';

}

function handleTouchEnd(e) {
  // e.preventDefault();
  if (activeEvent === 'move') {
    let receiver = null;
    for (let rId of receiverIds) {
      let r = document.getElementById(rId);
      if (detectContainment(e.target, r)) {
        receiver = r;
        break;
      }
    }
    if (receiver && receiver.classList.contains('unlocked')) {
      receiver.innerText = '';
      receiver.appendChild(e.target);
      e.target.removeEventListener('touchstart', handleTouchStart, {passive: true});
      e.target.removeEventListener('touchmove', handleTouchMove, {passive: true});
      e.target.removeEventListener('touchend', handleTouchEnd, {passive: true});
      receiver.classList.remove('unlocked');
    } else {
      e.target.style.position = 'initial';
    }
  }
  activeEvent = '';
}

function detectContainment(mover, receiver) {
  let m = mover.getBoundingClientRect();
  let r = receiver.getBoundingClientRect();
  return ((m.x + m.width) < (r.x + r.width) && m.x > r.x && m.y > r.y && (m.y + m.height) < (r.y + r.height));
}

document.addEventListener("DOMContentLoaded", function (event) {
  // add the proper listeners for a drag-and-drop receptacle
  const receptacles = document.getElementsByClassName('receptacle');
  for (let receiver of receptacles) {
    receiver.addEventListener('dragenter', handleDragEnter, false);
    receiver.addEventListener('dragover', handleDragOver, false);
    receiver.addEventListener('dragleave', handleDragLeave, false);
    receiver.addEventListener('drop', handleDrop, false);
    receiver.classList.add('unlocked');
    receiverIds.push(receiver.id);
  }

  // add the proper listeners for a draggable object
  // html objects need draggable=true
  const movables = document.getElementsByClassName('mover');
  for (let obj of movables) {
    obj.setAttribute("draggable", "true");
    obj.addEventListener('dragstart', handleDragStart, false);
    obj.addEventListener('dragend', handleDragEnd, false);
    obj.addEventListener('touchstart', handleTouchStart, {passive: true});
    obj.addEventListener('touchmove', handleTouchMove, {passive: true});
    obj.addEventListener('touchend', handleTouchEnd, {passive: true});
  }
});

// window.onload = function () {
//   // add the proper listeners for a drag-and-drop receptacle
//   const receptacles = document.getElementsByClassName('receptacle');
//   for (let receiver of receptacles) {
//     receiver.addEventListener('dragenter', handleDragEnter, false);
//     receiver.addEventListener('dragover', handleDragOver, false);
//     receiver.addEventListener('dragleave', handleDragLeave, false);
//     receiver.addEventListener('drop', handleDrop, false);
//     receiver.classList.add('unlocked');
//     receiverIds.push(receiver.id);
//   }
//
//   // add the proper listeners for a draggable object
//   // html objects need draggable=true
//   const movables = document.getElementsByClassName('mover');
//   for (let obj of movables) {
//     obj.setAttribute("draggable", "true");
//     obj.addEventListener('dragstart', handleDragStart, false);
//     obj.addEventListener('dragend', handleDragEnd, false);
//     obj.addEventListener('touchstart', handleTouchStart, {passive: true});
//     obj.addEventListener('touchmove', handleTouchMove, {passive: true});
//     obj.addEventListener('touchend', handleTouchEnd, {passive: true});
//   }


