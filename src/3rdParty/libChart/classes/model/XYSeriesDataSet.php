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
     * This dataset comprises several series of points and is used to plot multiple lines charts.
     * Each serie is a XYDataSet.
     *
     * @author Jean-Marc Tr�meaux (jm.tremeaux at gmail.com)
     * Created on 20 july 2007
     */
    class XYSeriesDataSet extends DataSet {

        private $titleList;
    

        private $serieList;
        

        public function __construct() {
            $this->titleList = array();
            $this->serieList = array();
        }
    

        public function addSerie($title, $serie) {
            array_push($this->titleList, $title);
            array_push($this->serieList, $serie);
        }

        public function getTitleList() {
            return $this->titleList;
        }


        public function getSerieList() {
            return $this->serieList;
        }
    }
?>