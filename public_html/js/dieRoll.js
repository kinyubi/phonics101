

function dieRoll(dieId, valueId) {
    // keep track of the number we roll to avoid duplicates
    if (typeof dieRoll.recentRolls === 'undefined') {dieRoll.recentRolls = [];}
    let number = Math.floor((Math.random() * 6) + 1);
    while (dieRoll.recentRolls.includes(number)) {number = Math.floor((Math.random() * 6) + 1);}
    dieRoll.recentRolls.push(number);
    if (dieRoll.recentRolls.length > 5) {dieRoll.recentRolls.shift();}

    // put the value in the element we've designated to hold the value
    document.getElementById(valueId).innerHTML = number + "";

    // create the rolling dice animation
    const dice = {
        position0: {y: 0, rotateX: 0, rotateY: 0},
        position1: {y: 0, rotateX: 1080, rotateY: -180},
        position2: {y: 0, rotateX: -1080, rotateY: -180},
        position3: {y: 0, rotateX: -1080, rotateY: -90},
        position4: {y: 0, rotateX: -990, rotateY: -360},
        position5: {y: 0, rotateX: -810, rotateY: -360},
        position6: {y: 0, rotateX: -1080, rotateY: -270},
    };
    let theDie = document.getElementById(dieId);
    theDie.transition(dice.position0, 0);
    theDie.transition(dice['position' + number], 1300, 'linear');
}
