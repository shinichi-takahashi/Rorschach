<?php

namespace Rorschach\Assert;

use GuzzleHttp\Psr7\Response;
use Rorschach\Parser;

class Type
{
    private $response;
    private $col;
    private $expect;

    /**
     * Type constructor.
     * @param Response $response
     * @param $col
     * @param $expect
     */
    public function __construct(Response $response, $col, $expect)
    {
        $this->response = $response;
        $this->col = $col;
        $this->expect = $expect;
    }

    /**
     * @return array|bool
     * @throws \Exception
     */
    public function assert()
    {
        $body = json_decode((string)$this->response->getBody(), true);
        $val = Parser::search($this->col, $body);

        $expects = explode('|', $this->expect);
        $nullable = in_array('nullable', $expects);

        $errors = [];
        foreach ($expects as $type) {
            // if given nullable and value is null, skip.
            if ($nullable && is_null($val)) {
                continue;
            }

            $type = strtolower($type);
            switch ($type) {
                case 'str':
                case 'string':
                    if (!is_string($val)) {
                        $errors[] = "{$val} is not string.";
                    }
                    break;
                case 'int':
                case 'integer':
                    if (!$val == (int)$val) {
                        $errors[] = "{$val} is not integer";
                    }
                    break;
                case 'double':
                case 'float':
                    if (!$val == (float)$val) {
                        $errors[] = "{$val} is not float.";
                    }
                    break;
                case 'array':
                    if (!array_values($val) === $val) {
                        $errors[] = "{$val} is not array.";
                    }
                    break;
                case 'obj':
                case 'object':
                    if (!array_values($val) !== $val) {
                        $errors[] = "{$val} is not object";
                    }
                    break;
                default:
                    throw new \Exception('Unknown type selected.');
            }
        }

        return $errors;
    }
}

;