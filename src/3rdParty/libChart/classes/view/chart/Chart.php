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

    /*! \mainpage Libchart
     *
     * This is the reference API, automatically compiled by <a href="http://www.stack.nl/~dimitri/doxygen/">Doxygen</a>.
     * You can find here information that is not covered by the <a href="../samplecode/">tutorial</a>.
     *
     */

    /**
     * Base chart class.
     *
     * @author Jean-Marc Tr�meaux (jm.tremeaux at gmail.com)
     */
    abstract class Chart {
        /**
         * The chart configuration.
         */
        protected $config;

        /**
         * The data set.
         */
        protected $dataSet;

        /**
         * Plot (holds graphical attributes).
         */
        protected $plot;

        /**
         * Abstract constructor of Chart.
         *
         * @param integer width of the image
         * @param integer height of the image
         */
        protected function __construct($width, $height) {
            // Initialize the configuration
            $this->config = new ChartConfig();

            // Creates the plot
            $this->plot = new Plot($width, $height);
            $this->plot->setTitle("Untitled chart");


// tb-modified removed the logo, want to give credit but it's too big for some of my graphs
//            $this->plot->setLogoFileName(dirname(__FILE__) . "/../../../images/PoweredBy.png");
        }

        /**
         * Checks the data model before rendering the graph.
         */
        protected function checkDataModel() {
            // Check if a dataset was defined
            if (!$this->dataSet) {
                die("Error: No dataset defined.");
            }

            // Maybe no points are defined, but that's ok. This will yield and empty graph with default boundaries.
        }

        protected function createImage() {
            $this->plot->createImage();
        }

        public function setDataSet($dataSet) {
            $this->dataSet = $dataSet;
        }


        public function getConfig() {
            return $this->config;
        }

        public function getPlot() {
            return $this->plot;
        }

        public function setTitle($title) {
            $this->plot->setTitle($title);
        }
    }
?>