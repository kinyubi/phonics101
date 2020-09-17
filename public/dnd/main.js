// @see: https://github.com/deepakkadarivel/DnDWithTouch/blob/master/index.html

let droppedIn = false;
let activeEvent = '';
let originalX = '';
let originalY = '';


window.onload = function () {
    // add the proper listeners for a drag-and-drop receptacle
    const receptacles = document.getElementsByClassName('receptacle');
    for (let obj of receptacles) {
        obj.addEventListener('dragenter', handleDragEnter, false);
        obj.addEventListener('dragover', handleDragOver, false);
        obj.addEventListener('dragleave', handleDragLeave, false);
        obj.addEventListener('drop', handleDrop, false);
    }

    // add the proper listeners for a draggable object
    // html objects need draggable=true
    const movables = document.getElementsByClassName('mover');
    for (let obj of movables) {
        obj.setAttribute("draggable", "true");
        obj.addEventListener('dragstart', handleDragStart, false);
        obj.addEventListener('dragend', handleDragEnd, false);
        obj.addEventListener('touchstart', handleTouchStart, false);
        obj.addEventListener('touchmove', handleTouchMove, false);
        obj.addEventListener('touchend', handleTouchEnd, false);
    }

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
        let element_id = e.dataTransfer.getData("text");
        e.target.appendChild(document.getElementById(element_id));
        document.getElementById(element_id).removeAttribute("draggable")
        document.getElementById(element_id).style.cursor = "default";
        droppedIn = true;
    }



    function handleDragStart(e) {
        e.dataTransfer.dropEffect = "move";
        e.dataTransfer.setData("text", e.target.getAttribute('id'));
    }

    function handleDragEnd(e) {
        droppedIn = false;
    }

    function handleTouchStart(e) {
        originalX = (e.target.offsetLeft - 10) + "px";
        originalY = (e.target.offsetTop - 10) + "px";
        activeEvent = 'start';
    }

    function handleTouchMove(e) {
        let touchLocation = e.targetTouches[0];
        let pageX = (touchLocation.pageX - 50) + "px";
        let pageY = (touchLocation.pageY - 50) + "px";
        e.target.style.position = "absolute";
        e.target.style.left = pageX;
        e.target.style.top = pageY;
        activeEvent = 'move';
    }

    function handleTouchEnd(e) {
        e.preventDefault();
        if (activeEvent === 'move') {
            let pageX = (parseInt(e.target.style.left) - 50);
            let pageY = (parseInt(e.target.style.top) - 50);
            let data = e.dataTransfer.getData("text");
            let obj = document.getElementById(data);
            if (detectTouchEnd(obj.offsetLeft, obj.offsetTop, pageX, pageY, obj.offsetWidth, obj.offsetHeight)) {
                e.target.appendChild(obj);
                e.target.style.position = "initial";
                droppedIn = true;
            } else {
                e.target.style.left = originalX;
                e.target.style.top = originalY;
            }
        }
    }

    function detectTouchEnd(x1, y1, x2, y2, w, h) {
        //Very simple detection here
        if (x2 - x1 > w) {
            return false;

        } else {
            return y2 - y1 <= h;

        }

    }
}
