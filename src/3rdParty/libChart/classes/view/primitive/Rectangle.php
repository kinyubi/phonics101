<?php


/* Copyright (C) 2019 Tom Berend

/* This program is free software: you can redistribute it and/or modify
/* it under the terms of the GNU General Public License as published by
/* the Free Software Foundation, either version 3 of the License, or
/* (at your option) any later version.
/* 
/* This program is distributed in the hope that it will be useful,
/* but WITHOUT ANY WARRANTY; without even the implied warranty of
/* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
/* GNU General Public License for more details.
/* 
/* You should have received a copy of the GNU General Public License
/* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


    /* Libchart - PHP chart library
     * Copyright (C) 2005-2011 Jean-Marc Tr�meaux (jm.tremeaux at gmail.com)
     * 
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation, either version 3 of the License, or
     * (at your option) any later version.
     * 
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License
     * along with this program.  If not, see <http://www.gnu.org/licenses/>.
     * 
     */
    
    /**
     * A rectangle identified by the top-left and the bottom-right corners.
     *
     * @author Jean-Marc Tr�meaux (jm.tremeaux at gmail.com)
     * @Created on 27 july 2007
     */
    class Rectangle {

        public $x1;
        public $y1;
        public $x2;
        public $y2;
    

        public function __construct($x1, $y1, $x2, $y2) {
            $this->x1 = $x1;
            $this->y1 = $y1;
            $this->x2 = $x2;
            $this->y2 = $y2;
        }

        public function getPaddedRectangle($padding) {
            $rectangle = new Rectangle(
                    $this->x1 + $padding->left,
                    $this->y1 + $padding->top,
                    $this->x2 - $padding->right,
                    $this->y2 - $padding->bottom
            );
            
            //echo "(" . $this->x1 . "," . $this->y1 . ") (" . $this->x2 . "," . $this->y2 . ")<br>";
            return $rectangle;
        }
    }
?>