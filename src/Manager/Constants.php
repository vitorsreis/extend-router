<?php

/**
 * This file is part of d5whub extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace D5WHUB\Extend\Router\Manager;

abstract class Constants
{
    const REGEX_MATCH = '~(\*|/|:[a-zA-Z_]\w*(?:\[\w*])?|\[\w*\])~';

    const REGEX_VARIABLE = '~:(\w+)(?:\[(\w*)])?~';

    const REGEX_LOOSE_FILTER = '~\[(\w*)]~';

    const PREG_SPLIT_FLAGS = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;

    const PATTERN_FILTER_KEY = '~\W~';

    const INDEXES_PATTERN_MAX_CHUCK = 100;

    const INDEXER_PATTERN_MAX_LENGTH = 10000;
}
