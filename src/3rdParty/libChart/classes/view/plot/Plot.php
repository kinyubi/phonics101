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
     * The plot holds graphical attributes, and is responsible for computing the layout of the graph.
     * The layout is quite simple right now, with 4 areas laid out like that:
     * (of course this is subject to change in the future).
     *
     * output area------------------------------------------------|
     * |  (outer padding)                                         |
     * |  image area--------------------------------------------| |
     * |  | (title padding)                                     | |
     * |  | title area----------------------------------------| | |
     * |  | |-------------------------------------------------| | |
     * |  |                                                     | |
     * |  | (graph padding)              (caption padding)      | |
     * |  | graph area----------------|  caption area---------| | |
     * |  | |                         |  |                    | | |
     * |  | |                         |  |                    | | |
     * |  | |                         |  |                    | | |
     * |  | |                         |  |                    | | |
     * |  | |                         |  |                    | | |
     * |  | |-------------------------|  |--------------------| | |
     * |  |                                                     | |
     * |  |-----------------------------------------------------| |
     * |                                                          |
     * |----------------------------------------------------------|
     *
     * All area dimensions are known in advance , and the optional logo is drawn in absolute coordinates.
     *
     * @author Jean-Marc Tr�meaux (jm.tremeaux at gmail.com)
     * Created on 27 july 2007
     */
    class Plot {
        // Style properties
        protected string $title;

        /**
         * Location of the logo. Can be overriden to your personalized logo.
         */
        protected string $logoFileName;

        /**
         * Outer area, whose dimension is the same as the PNG returned.
         */
        protected Rectangle $outputArea;

        /**
         * Outer padding surrounding the whole image, everything outside is blank.
         */
        protected Padding $outerPadding;

        /**
         * Coordinates of the area inside the outer padding.
         */
        protected Rectangle $imageArea;

        /**
         * Fixed title height in pixels.
         */
        protected int $titleHeight;

        /**
         * Padding of the title area.
         */
        protected Padding $titlePadding;

        /**
         *  Coordinates of the title area.
         */
        protected Rectangle $titleArea;

        /**
         * True if the plot has a caption.
         */
        protected bool $hasCaption;

        /**
         * Ratio of graph/caption in width.
         */
        protected float $graphCaptionRatio;

        /**
         * Padding of the graph area.
         */
        protected Padding $graphPadding;

        /**
         * Coordinates of the graph area.
         */
        protected Rectangle $graphArea;

        /**
         * Padding of the caption area.
         */
        protected Padding $captionPadding;

        /**
         * Coordinates of the caption area.
         */
        protected Rectangle $captionArea;

        /**
         * Text writer.
         */
        protected Text $text;

        /**
         * Color palette.
         */
        protected Palette $palette;

        /**
         * @var bool|resource
         */
        protected  $img;

        /**
         * Drawing primitives
         */
        protected Primitive $primitive;

        protected Color $backGroundColor;
        protected Color $textColor;
        protected int $width;
        protected int $height;

        /**
         * Constructor of Plot.
         *
         * @param integer width of the image
         * @param integer height of the image
         */
        public function __construct($width, $height) {
            $this->width = $width;
            $this->height = $height;

            $this->text = new Text();
            $this->palette = new Palette();

            // Default layout
            $this->outputArea = new Rectangle(0, 0, $width - 1, $height - 1);
            $this->outerPadding = new Padding(5);
            $this->titleHeight = 26;
            $this->titlePadding = new Padding(5);
            $this->hasCaption = false;
            $this->graphCaptionRatio = 0.50;
            $this->graphPadding = new Padding(50);
            $this->captionPadding = new Padding(15);
        }

        /**
         * Compute the area inside the outer padding (outside is white).
         */
        private function computeImageArea() : void {
            $this->imageArea = $this->outputArea->getPaddedRectangle($this->outerPadding);
        }

        /**
         * Compute the title area.
         */
        private function computeTitleArea() : void {
            $titleUnpaddedBottom = $this->imageArea->y1 + $this->titleHeight + $this->titlePadding->top + $this->titlePadding->bottom;
            $titleArea = new Rectangle(
                    $this->imageArea->x1,
                    $this->imageArea->y1,
                    $this->imageArea->x2,
                    $titleUnpaddedBottom - 1
            );
            $this->titleArea = $titleArea->getPaddedRectangle($this->titlePadding);
        }

        /**
         * Compute the graph area.
         */
        private function computeGraphArea() : void {
            $titleUnpaddedBottom = $this->imageArea->y1 + $this->titleHeight + $this->titlePadding->top + $this->titlePadding->bottom;
            $graphArea = null;
            if ($this->hasCaption) {
                $graphUnpaddedRight = $this->imageArea->x1 + ($this->imageArea->x2 - $this->imageArea->x1) * $this->graphCaptionRatio
                        + $this->graphPadding->left + $this->graphPadding->right;
                $graphArea = new Rectangle(
                        $this->imageArea->x1,
                        $titleUnpaddedBottom,
                        $graphUnpaddedRight - 1,
                        $this->imageArea->y2
                );
            } else {
                $graphArea = new Rectangle(
                        $this->imageArea->x1,
                        $titleUnpaddedBottom,
                        $this->imageArea->x2,
                        $this->imageArea->y2
                );
            }
            $this->graphArea = $graphArea->getPaddedRectangle($this->graphPadding);
        }

        /**
         * Compute the caption area.
         */
        private function computeCaptionArea() : void {
            $graphUnpaddedRight = $this->imageArea->x1 + ($this->imageArea->x2 - $this->imageArea->x1) * $this->graphCaptionRatio
                    + $this->graphPadding->left + $this->graphPadding->right;
            $titleUnpaddedBottom = $this->imageArea->y1 + $this->titleHeight + $this->titlePadding->top + $this->titlePadding->bottom;
            $captionArea = new Rectangle(
                    $graphUnpaddedRight,
                    $titleUnpaddedBottom,
                    $this->imageArea->x2,
                    $this->imageArea->y2
            );
            $this->captionArea = $captionArea->getPaddedRectangle($this->captionPadding);
        }

        /**
         * Compute the layout of all areas of the graph.
         */
        public function computeLayout() : void {
            $this->computeImageArea();
            $this->computeTitleArea();
            $this->computeGraphArea();
            if ($this->hasCaption) {
                $this->computeCaptionArea();
            }
        }

        /**
         * Creates and initialize the image.
         */
        public function createImage() : void {
 
            $this->img = imagecreatetruecolor($this->width, $this->height);
 
            $this->primitive = new Primitive($this->img);

            $this->backGroundColor = new Color(255, 255, 255);
            $this->textColor = new Color(0, 0, 0);

            // White background
            imagefilledrectangle($this->img, 0, 0, $this->width - 1, $this->height - 1, $this->backGroundColor->getColor($this->img));

        }

        /**
         * Print the title to the image.
         */
        public function printTitle() : void {
            $yCenter = $this->titleArea->y1 + ($this->titleArea->y2 - $this->titleArea->y1) / 2;
            $this->text->printCentered($this->img, $yCenter, $this->textColor, $this->title, $this->text->fontCondensedBold);
        }

        /**
         * Print the logo image to the image.
         */
        public function printLogo() : void {
            if(!empty($this->logoFileName)){
                @$logoImage = imageCreateFromPNG($this->logoFileName);

                if ($logoImage) {
                    imagecopymerge($this->img, $logoImage, 2 * $this->outerPadding->left, $this->outerPadding->top, 0, 0, imagesx($logoImage), imagesy($logoImage), 100);
                }
            }
        }

        public function render(string $fileName) : void {
            if (isset($fileName)) {
                imagepng($this->img, $fileName);
            } else {
                imagepng($this->img);
            }
        }


        public function setTitle(string $title) : void {
            $this->title = $title;
        }


        public function setLogoFileName(string $logoFileName) : void {
            $this->logoFileName = $logoFileName;
        }

        /**
         * return a GD image
         * @return bool|resource
         */
        public function getImg() {
            return $this->img;
        }


        public function getPalette() : Palette {
            return $this->palette;
        }

        public function getText() : Text {
            return $this->text;
        }

        public function getPrimitive() : Primitive {
            return $this->primitive;
        }

        public function getOuterPadding() : Padding {
            return $this->outerPadding;
        }

        public function setOuterPadding($outerPadding) : void {
            $this->outerPadding = $outerPadding;
        }


        public function setTitleHeight($titleHeight) : void {
            $this->titleHeight = $titleHeight;
        }

        public function setTitlePadding($titlePadding) : void {
            $this->titlePadding = $titlePadding;
        }

        public function setGraphPadding($graphPadding) : void {
            $this->graphPadding = $graphPadding;
        }

        public function setHasCaption($hasCaption) : void {
            $this->hasCaption = $hasCaption;
        }

        public function setCaptionPadding($captionPadding) : void  {
            $this->captionPadding = $captionPadding;
        }

        public function setGraphCaptionRatio($graphCaptionRatio) : void {
            $this->graphCaptionRatio = $graphCaptionRatio;
        }

        public function getGraphArea() : Rectangle {
            return $this->graphArea;
        }

        public function getCaptionArea() : Rectangle {
            return $this->captionArea;
        }

        public function getTextColor() : Color {
            return $this->textColor;
        }
    }
