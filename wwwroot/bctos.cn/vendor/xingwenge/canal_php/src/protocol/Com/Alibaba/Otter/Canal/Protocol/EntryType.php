<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: EntryProtocol.proto

namespace Com\Alibaba\Otter\Canal\Protocol;

use UnexpectedValueException;

/**
 **打散后的事件类型，主要用于标识事务的开始，变更数据，结束*
 *
 * Protobuf type <code>com.alibaba.otter.canal.protocol.EntryType</code>
 */
class EntryType
{
    /**
     * Generated from protobuf enum <code>ENTRYTYPECOMPATIBLEPROTO2 = 0;</code>
     */
    const ENTRYTYPECOMPATIBLEPROTO2 = 0;
    /**
     * Generated from protobuf enum <code>TRANSACTIONBEGIN = 1;</code>
     */
    const TRANSACTIONBEGIN = 1;
    /**
     * Generated from protobuf enum <code>ROWDATA = 2;</code>
     */
    const ROWDATA = 2;
    /**
     * Generated from protobuf enum <code>TRANSACTIONEND = 3;</code>
     */
    const TRANSACTIONEND = 3;
    /**
     ** 心跳类型，内部使用，外部暂不可见，可忽略 *
     *
     * Generated from protobuf enum <code>HEARTBEAT = 4;</code>
     */
    const HEARTBEAT = 4;
    /**
     * Generated from protobuf enum <code>GTIDLOG = 5;</code>
     */
    const GTIDLOG = 5;

    private static $valueToName = [
        self::ENTRYTYPECOMPATIBLEPROTO2 => 'ENTRYTYPECOMPATIBLEPROTO2',
        self::TRANSACTIONBEGIN => 'TRANSACTIONBEGIN',
        self::ROWDATA => 'ROWDATA',
        self::TRANSACTIONEND => 'TRANSACTIONEND',
        self::HEARTBEAT => 'HEARTBEAT',
        self::GTIDLOG => 'GTIDLOG',
    ];

    public static function name($value)
    {
        if (!isset(self::$valueToName[$value])) {
            throw new UnexpectedValueException(sprintf(
                    'Enum %s has no name defined for value %s', __CLASS__, $value));
        }
        return self::$valueToName[$value];
    }


    public static function value($name)
    {
        $const = __CLASS__ . '::' . strtoupper($name);
        if (!defined($const)) {
            throw new UnexpectedValueException(sprintf(
                    'Enum %s has no value defined for name %s', __CLASS__, $name));
        }
        return constant($const);
    }
}

