<?php

/**
 * This file is part of vsr extend router
 * @author Vitor Reis <vitor@d5w.com.br>
 * @phpcs:disable
 */

namespace VSR\Extend\Router\Manager;

abstract class Constants
{
    const REGEX_DELIMITER = '~';
    const REGEX_MATCH = self::REGEX_DELIMITER . '(\*|/|:[a-zA-Z_]\w*(?:\[\w*])?|\[\w*\])' . self::REGEX_DELIMITER;
    const REGEX_VARIABLE = self::REGEX_DELIMITER . ':(\w+)(?:\[(\w*)])?' . self::REGEX_DELIMITER;
    const REGEX_LOOSE_FILTER = self::REGEX_DELIMITER . '\[(\w*)]' . self::REGEX_DELIMITER;
    const REGEX_FILTER_KEY = self::REGEX_DELIMITER . '\W' . self::REGEX_DELIMITER;
    const PREG_SPLIT_FLAGS = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;
    const INDEXES_PATTERN_MAX_CHUCK = 100;
    const INDEXER_PATTERN_MAX_LENGTH = 10000;
}
