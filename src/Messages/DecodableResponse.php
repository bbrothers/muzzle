<?php

namespace Muzzle\Messages;

use Psr\Http\Message\ResponseInterface;

class DecodableResponse implements ResponseInterface
{

    use ResponseDecorator;
    use Statusable;
    use JsonMessage;
}
