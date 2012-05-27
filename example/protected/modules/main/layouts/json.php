<?php
\Eve::app()->response->header('Content-Type', 'application/json');
echo json_encode($this->_data);
