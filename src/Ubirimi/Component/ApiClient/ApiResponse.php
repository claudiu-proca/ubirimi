<?php

/*
 *  Copyright (C) 2012-2014 SC Ubirimi SRL <info-copyright@ubirimi.com>
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License version 2 as
 *  published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301, USA.
 */

namespace Ubirimi\Component\ApiClient;

class ApiResponse
{
    /**
     * Response Body
     *
     * @var mixed
     */
    private $content;

    /**
     * Http Status Code
     *
     * @var integer
     */
    private $statusCode;

    /**
     * Response Content Type
     *
     * @var string
     */
    private $contentType;

    public function __construct($content, $statusCode, $contentType)
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->contentType = $contentType;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }
}