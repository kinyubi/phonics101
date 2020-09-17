let button_reset;
let button_size;
let button_color_all;
let size_pop_up;
let line_horizontal = [];
let line_vertical = [];
let line_border = [];
let strip;
let strip_counter = 0;
let strip_dimension = 152;
let strip_dimension_half = strip_dimension / 2;
let strip_start_y = 252;
let strip_initial_start_x = [];
let strip_initial_start_y = 540;
let strip_rows = 2;
let strip_columns = 3;
let strip_columns_max = 6;
let border_left = [];
let border_right;
let border_top = 250;
let border_bottom;
let size_index = 1;
let size_total = 4;
let size_menu = [];
let size_menu_start_x = 0;
let size_menu_start_y = 90;
let size_menu_dimension = 92;
let color_index = 1;
let color_total = 6;
let color_menu = [];
let color_menu_start_x = 0;
let color_menu_start_y = 90;
let color_menu_dimension = 92;
let color_data = [];
let color_mix = false;
function init() {
    canvasInUse = true;
    document.getElementById('game_container').style.display = "inline";
    document.getElementById('loading_div').style.display = "none";
    setInteractiveParameters();
    set_data();
    button_reset = new MovieClip(document.getElementById('button_reset').cloneNode(true));
    button_reset.x = 720;
    button_reset.y = 55;
    button_reset.transform();
    stage.appendChild(button_reset.instance);
    button_color_all = new MovieClip(document.getElementById('button_color_all').cloneNode(true));
    button_color_all.x = 410;
    button_color_all.y = 55;
    button_color_all.transform();
    button_color_all.text = button_color_all.instance.querySelector(".color_all_on");
    stage.appendChild(button_color_all.instance);
    button_size = new MovieClip(document.getElementById('button_size').cloneNode(true));
    button_size.x = 615;
    button_size.y = 55;
    button_size.transform();
    button_size.text = new MovieClip(button_size.instance.querySelector(".size"));
    button_size.text.instance.textContent = Number(size_index) + 1;
    stage.appendChild(button_size.instance);
    size_pop_up = new MovieClip(document.getElementById('size_pop_up').cloneNode(true));
    size_pop_up.x = button_size.x;
    size_pop_up.y = button_size.y;
    size_pop_up.transform();
    size_pop_up.instance.setAttribute("display", "none");
    for (let i = 1; i <= size_total; i++) {
        size_menu[i] = new MovieClip(document.getElementById('size_menu').cloneNode(true));
        size_menu[i].x = size_menu_start_x;
        size_menu[i].y = size_menu_start_y + (i - 1) * size_menu_dimension;
        size_menu[i].instance.setAttribute("data-id", i);
        size_menu[i].text = new MovieClip(size_menu[i].instance.querySelector(".size"));
        size_menu[i].text.instance.textContent = (i + 1);
        size_menu[i].transform();
        size_pop_up.instance.appendChild(size_menu[i].instance);
    }
    button_color = new MovieClip(document.getElementById('button_color').cloneNode(true));
    button_color.x = 510;
    button_color.y = 55;
    button_color.transform();
    button_color.area = new MovieClip(button_color.instance.querySelector(".area"));
    button_color.area.instance.setAttribute("fill", color_data[color_index]);
    stage.appendChild(button_color.instance);
    color_pop_up = new MovieClip(document.getElementById('color_pop_up').cloneNode(true));
    color_pop_up.x = button_color.x;
    color_pop_up.y = button_color.y;
    color_pop_up.transform();
    color_pop_up.instance.setAttribute("display", "none");
    for (let i = 1; i <= color_total; i++) {
        color_menu[i] = new MovieClip(document.getElementById('color_menu').cloneNode(true));
        color_menu[i].x = color_menu_start_x;
        color_menu[i].y = color_menu_start_y + (i - 1) * color_menu_dimension;
        color_menu[i].instance.setAttribute("data-id", i);
        color_menu[i].area = new MovieClip(color_menu[i].instance.querySelector(".area"));
        color_menu[i].area.instance.setAttribute("fill", color_data[i]);
        color_menu[i].transform();
        color_pop_up.instance.appendChild(color_menu[i].instance);
    }
    for (let i = 1; i <= strip_rows; i++) {
        line_horizontal[i] = new MovieClip(document.getElementById('line_horizontal').cloneNode(true));
        line_horizontal[i].y = strip_start_y - 2 + (i - 1) * strip_dimension;
        line_horizontal[i].transform();
        stage.appendChild(line_horizontal[i].instance);
    }
    border_bottom = line_horizontal[strip_rows].y;
    for (let i = 1; i <= strip_columns_max; i++) {
        line_vertical[i] = new MovieClip(document.getElementById('line_vertical').cloneNode(true));
        line_vertical[i].y = border_top;
        stage.appendChild(line_vertical[i].instance);
    }
    line_border[1] = new MovieClip(document.getElementById('line_horizontal').cloneNode(true));
    line_border[2] = new MovieClip(document.getElementById('line_horizontal').cloneNode(true));
    line_border[3] = new MovieClip(document.getElementById('line_vertical').cloneNode(true));
    line_border[4] = new MovieClip(document.getElementById('line_vertical').cloneNode(true));
    stage.appendChild(line_border[1].instance);
    stage.appendChild(line_border[2].instance);
    stage.appendChild(line_border[3].instance);
    stage.appendChild(line_border[4].instance);
    line_border[1].x = line_horizontal[1].x;
    line_border[1].y = line_horizontal[1].y - 2;
    line_border[1].transform();
    line_border[1].instance.setAttribute("stroke-width", 8);
    line_border[2].x = line_horizontal[strip_rows].x;
    line_border[2].y = line_horizontal[strip_rows].y + 2;
    line_border[2].transform();
    line_border[2].instance.setAttribute("stroke-width", 8);
    line_border[3].instance.setAttribute("y1", -6);
    line_border[3].instance.setAttribute("y2", 158);
    line_border[3].instance.setAttribute("stroke-width", 8);
    line_border[4].instance.setAttribute("y1", -6);
    line_border[4].instance.setAttribute("y2", 158);
    line_border[4].instance.setAttribute("stroke-width", 8);
    set_game();
    add_pointer_listeners();
}
function set_game() {
    hide_pop_ups();
    for (let i = 1; i <= strip_counter; i++) {
        stage.removeChild(strip[i].instance);
    }
    strip_counter = 0;
    strip = [];
    for (let i = 1; i <= strip_columns_max; i++) {
        line_vertical[i].instance.setAttribute("display", "none");
    }
    for (let i = 1; i <= strip_columns; i++) {
        line_vertical[i].instance.setAttribute("display", "inline");
    }
    for (let i = 1; i <= strip_columns; i++) {
        line_vertical[i].x = border_left[strip_columns] + (i - 1) * strip_dimension;
        line_vertical[i].transform();
    }
    border_right = line_vertical[strip_columns].x;
    let left_parameter = border_left[strip_columns];
    let right_parameter = left_parameter + strip_dimension * (strip_columns - 1);
    for (let i = 1; i <= strip_rows; i++) {
        line_horizontal[i].instance.setAttribute("x1", left_parameter);
        line_horizontal[i].instance.setAttribute("x2", right_parameter);
    }
    line_border[1].instance.setAttribute("x1", left_parameter);
    line_border[1].instance.setAttribute("x2", right_parameter);
    line_border[2].instance.setAttribute("x1", left_parameter);
    line_border[2].instance.setAttribute("x2", right_parameter);
    line_border[3].x = border_left[strip_columns];
    line_border[3].y = line_vertical[1].y;
    line_border[3].transform();
    line_border[4].x = line_vertical[strip_columns].x + 2;
    line_border[4].y = line_vertical[strip_columns].y;
    line_border[4].transform();
    for (let i = 1; i < strip_columns; i++) {
        strip_item(strip_initial_start_x[strip_columns] + (i - 1) * strip_dimension, strip_initial_start_y);
    }
    set_color();
}
function strip_item(x, y) {
    strip_counter++;
    strip[strip_counter] = new MovieClip(document.getElementById('strip').cloneNode(true));
    strip[strip_counter].start_x = x;
    strip[strip_counter].start_y = y;
    strip[strip_counter].x = x;
    strip[strip_counter].y = y;
    strip[strip_counter].transform();
    strip[strip_counter].instance.setAttribute("data-id", strip_counter);
    strip[strip_counter].area = new MovieClip(strip[strip_counter].instance.querySelector(".area"));
    strip[strip_counter].area.instance.setAttribute("fill", color_data[color_index]);
    strip[strip_counter].dragFront = true;
    strip[strip_counter].dragMoveHandler = function() {}
    strip[strip_counter].dragStopHandler = function() {
        if (this.y < strip_start_y + strip_dimension && this.x > border_left[strip_columns] && this.x < border_right && this.y > border_top && this.y < border_bottom) {
            this.target_y = strip_start_y - strip_dimension / 2 + strip_dimension;
            this.row_id = 2;
        }
        sort_strips();
    }
    strip[strip_counter].instance.addEventListener("pointerdown", strip_handler);
    stage.appendChild(strip[strip_counter].instance);
}
function set_color() {
    if (color_mix) {
        for (let i = 1; i < strip_columns; i++) {
            strip[i].area.instance.setAttribute("fill", color_data[i]);
        }
    } else {
        for (let i = 1; i < strip_columns; i++) {
            strip[i].area.instance.setAttribute("fill", color_data[color_index]);
        }
    }
}
function row_list(row_id) {
    let list = [];
    for (let i = 1; i < strip_columns; i++) {
        if (strip[i].row_id === row_id) {
            list.push(strip[i]);
        }
    }
    return list;
}
function compare(a, b) {
    if (a.x < b.x) {
        return -1;
    }
    if (a.x > b.x) {
        return 1;
    }
    return 0;
}
function column_checker(strip_holder) {
    let list = [];
    for (let i = 1; i <= strip_columns; i++) {
        list[i] = {};
        list[i].index = i;
        list[i].x = Math.abs(strip_holder.x - strip_dimension_half - line_vertical[i].x);
    }
    list.sort(compare);
    return line_vertical[list[0].index].x + strip_dimension_half;
}
function sort_strips() {
    let list;
    list = row_list(2);
    if (list.length > 0) {
        list.sort(compare);
        for (let i = 0; i < list.length; i++) {
            list[i].current_x = list[i].x;
            list[i].x = column_checker(list[i]);
        }
        if (list[0].x + strip_dimension_half > border_right) {
            list[0].row_id = 0;
            list[0].tweenStart(200, 'current', list[0].target_y, 'current', 'current', 'current');
        } else {
            list[0].tweenStart(200, 'current', list[0].target_y, 'current', 'current', 'current');
            for (let i = 1; i < list.length; i++) {
                list[i].target_x = list[i].x;
                if (list[i].x - strip_dimension_half < list[i - 1].x + strip_dimension_half) {
                    list[i].x = list[i - 1].x + strip_dimension;
                    list[i].target_x = list[i].x;
                }
            }
            for (let i = 1; i < list.length; i++) {
                if (list[i].x + strip_dimension_half > border_right) {
                    list[i].row_id = 0;
                }
                list[i].x = list[i].current_x;
                list[i].tweenStart(300, list[i].target_x, list[i].target_y, 'current', 'current', 'current');
            }
        }
    }
}
function hide_pop_ups() {
    button_size.instance.setAttribute("display", "inline");
    size_pop_up.instance.setAttribute("display", "none");
    if (color_mix) {
        button_color.instance.setAttribute("display", "none");
    } else {
        button_color.instance.setAttribute("display", "inline");
    }
    color_pop_up.instance.setAttribute("display", "none");
}
function strip_handler(event) {
    event.preventDefault();
    if (event.isPrimary) {
        if (!this.tweening) {
            hide_pop_ups();
            dragElement = strip[this.getAttribute("data-id")];
            dragElement.row_id = 0;
            sort_strips();
            dragPointerStart(event);
        }
    }
}
function reset_handler(event) {
    event.preventDefault();
    if (event.isPrimary) {
        hide_pop_ups();
        for (let i = 1; i < strip_columns; i++) {
            strip[i].x = strip[i].start_x;
            strip[i].y = strip[i].start_y;
            strip[i].transform();
            strip[i].row_id = 0;
        }
    }
}
function size_handler(event) {
    event.preventDefault();
    if (event.isPrimary) {
        size_index = this.getAttribute("data-id");
        strip_columns = (Number(size_index) + 2);
        button_size.text.instance.textContent = (Number(size_index) + 1);
        set_game();
    }
}
function size_pop_up_handler(event) {
    event.preventDefault();
    if (event.isPrimary) {
        hide_pop_ups();
        button_size.instance.setAttribute("display", "none");
        stage.appendChild(size_pop_up.instance);
        size_pop_up.instance.setAttribute("display", "inline");
    }
}
function color_handler(event) {
    event.preventDefault();
    if (event.isPrimary) {
        color_index = this.getAttribute("data-id");
        button_color.area.instance.setAttribute("fill", color_data[color_index]);
        set_game();
    }
}
function color_pop_up_handler(event) {
    event.preventDefault();
    if (event.isPrimary) {
        hide_pop_ups();
        button_color.instance.setAttribute("display", "none");
        stage.appendChild(color_pop_up.instance);
        color_pop_up.instance.setAttribute("display", "inline");
    }
}
function button_color_all_handler(event) {
    event.preventDefault();
    if (event.isPrimary) {
        if (color_mix) {
            button_color.instance.setAttribute("display", "inline");
            color_mix = false;
            button_color_all.text.textContent = "On"
        } else {
            button_color.instance.setAttribute("display", "none");
            color_mix = true;
            button_color_all.text.textContent = "Off"
        }
        set_game();
    }
}
function set_data() {
    color_data[1] = '#40a4ff';
    color_data[2] = '#ff8100';
    color_data[3] = '#0EBE1F';
    color_data[4] = '#fdf42c';
    color_data[5] = '#fd668c';
    color_data[6] = '#cb5bf2';
    border_left[3] = 235;
    border_left[4] = 165;
    border_left[5] = 85;
    border_left[6] = 10;
    strip_initial_start_x[3] = 310;
    strip_initial_start_x[4] = 240;
    strip_initial_start_x[5] = 165;
    strip_initial_start_x[6] = 84;
}
function add_pointer_listeners() {
    button_color_all.instance.addEventListener("pointerdown", button_color_all_handler);
    button_reset.instance.addEventListener("pointerdown", reset_handler);
    button_color.instance.addEventListener("pointerdown", color_pop_up_handler);
    button_size.instance.addEventListener("pointerdown", size_pop_up_handler);
    for (let i = 1; i <= color_total; i++) {
        color_menu[i].instance.addEventListener("pointerdown", color_handler);
    }
    for (let i = 1; i <= size_total; i++) {
        size_menu[i].instance.addEventListener("pointerdown", size_handler);
    }
}
