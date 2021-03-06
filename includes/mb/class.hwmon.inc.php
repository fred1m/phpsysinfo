<?php
/**
 * hwmon sensor class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.hwmon.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * getting hardware sensors information from /sys/class/hwmon/hwmon
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @author    William Johansson <radar@radhuset.org>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class Hwmon extends Sensors
{
    /**
     * get temperature information
     *
     * @param  string $hwpath
     * @return void
     */
    protected function _temperature($hwpath)
    {
       $sensor = glob($hwpath."temp*_input", GLOB_NOSORT);
       if (($total = count($sensor)) > 0) {
            $buf = "";
            for ($i = 0; $i < $total; $i++) if (CommonFunctions::rfts($sensor[$i], $buf, 1, 4096, false) && (trim($buf) != "")) {
                $dev = new SensorDevice();
                $dev->setValue(trim($buf)/1000);
                $label = preg_replace("/_input$/", "_label", $sensor[$i]);
                $crit = preg_replace("/_input$/", "_crit", $sensor[$i]);
                $max = preg_replace("/_input$/", "_max", $sensor[$i]);
                $crit_alarm = preg_replace("/_input$/", "_crit_alarm", $sensor[$i]);
                $sname = preg_replace("/\/[^\/]*_input$/", "/name", $sensor[$i]);
                if (CommonFunctions::fileexists($sname) && CommonFunctions::rfts($sname, $buf, 1, 4096, false) && (trim($buf) != "")) {
                   $name = " (".trim($buf).")";
                } else {
                   $name = "";
                }
                if (CommonFunctions::fileexists($label) && CommonFunctions::rfts($label, $buf, 1, 4096, false) && (trim($buf) != "")) {
                    $dev->setName(trim($buf).$name);
                } else {
                    $labelname = trim(preg_replace("/_input$/", "", pathinfo($sensor[$i], PATHINFO_BASENAME)));
                    if ($labelname !== "") {
                        $dev->setName($labelname.$name);
                    } else {
                        $dev->setName('unknown'.$name);
                    }
                }
                if (CommonFunctions::fileexists($crit) && CommonFunctions::rfts($crit, $buf, 1, 4096, false) && (trim($buf) != "")) {
                    $dev->setMax(trim($buf)/1000);
                    if (CommonFunctions::fileexists($crit_alarm) && CommonFunctions::rfts($crit_alarm, $buf, 1, 4096, false) && (trim($buf) === "1")) {
                        $dev->setEvent("Critical Alarm");
                    }
                } elseif (CommonFunctions::fileexists($max) && CommonFunctions::rfts($max, $buf, 1, 4096, false) && (trim($buf) != "")) {
                    $dev->setMax(trim($buf)/1000);
                }
                $this->mbinfo->setMbTemp($dev);
            }
        }
    }

    /**
     * get voltage information
     *
     * @param  string $hwpath
     * @return void
     */
    private function _voltage($hwpath)
    {
       $sensor = glob($hwpath."in*_input", GLOB_NOSORT);
       if (($total = count($sensor)) > 0) {
            $buf = "";
            for ($i = 0; $i < $total; $i++) if (CommonFunctions::rfts($sensor[$i], $buf, 1, 4096, false) && (trim($buf) != "")) {
                $dev = new SensorDevice();
                $dev->setValue(trim($buf)/1000);
                $label = preg_replace("/_input$/", "_label", $sensor[$i]);
                $alarm = preg_replace("/_input$/", "_alarm", $sensor[$i]);
                $max = preg_replace("/_input$/", "_max", $sensor[$i]);
                $min = preg_replace("/_input$/", "_min", $sensor[$i]);
                $sname = preg_replace("/\/[^\/]*_input$/", "/name", $sensor[$i]);
                if (CommonFunctions::fileexists($sname) && CommonFunctions::rfts($sname, $buf, 1, 4096, false) && (trim($buf) != "")) {
                   $name = " (".trim($buf).")";
                } else {
                   $name = "";
                }
                if (CommonFunctions::fileexists($label) && CommonFunctions::rfts($label, $buf, 1, 4096, false) && (trim($buf) != "")) {
                    $dev->setName(trim($buf).$name);
                } else {
                    $labelname = trim(preg_replace("/_input$/", "", pathinfo($sensor[$i], PATHINFO_BASENAME)));
                    if ($labelname !== "") {
                        $dev->setName($labelname.$name);
                    } else {
                        $dev->setName('unknown'.$name);
                    }
                }
                if (CommonFunctions::fileexists($max) && CommonFunctions::rfts($max, $buf, 1, 4096, false) && (trim($buf) != "")) {
                    $dev->setMax(trim($buf)/1000);
                }
                if (CommonFunctions::fileexists($min) && CommonFunctions::rfts($min, $buf, 1, 4096, false) && (trim($buf) != "")) {
                    $dev->setMin(trim($buf)/1000);
                }
                if (CommonFunctions::fileexists($alarm) && CommonFunctions::rfts($alarm, $buf, 1, 4096, false) && (trim($buf) === "1")) {
                    $dev->setEvent("Alarm");
                }
                $this->mbinfo->setMbVolt($dev);
            }
        }
    }

    /**
     * get fan information
     *
     * @param  string $hwpath
     * @return void
     */
    protected function _fans($hwpath)
    {
       $sensor = glob($hwpath."fan*_input", GLOB_NOSORT);
       if (($total = count($sensor)) > 0) {
            $buf = "";
            for ($i = 0; $i < $total; $i++) if (CommonFunctions::rfts($sensor[$i], $buf, 1, 4096, false) && (trim($buf) != "")) {
                $dev = new SensorDevice();
                $dev->setValue(trim($buf));
                $label = preg_replace("/_input$/", "_label", $sensor[$i]);
                $alarm = preg_replace("/_input$/", "_alarm", $sensor[$i]);
                $fullmax = preg_replace("/_input$/", "_full_speed", $sensor[$i]);
                $max = preg_replace("/_input$/", "_max", $sensor[$i]);
                $min = preg_replace("/_input$/", "_min", $sensor[$i]);
                $sname = preg_replace("/\/[^\/]*_input$/", "/name", $sensor[$i]);
                if (CommonFunctions::fileexists($sname) && CommonFunctions::rfts($sname, $buf, 1, 4096, false) && (trim($buf) != "")) {
                   $name = " (".trim($buf).")";
                } else {
                   $name = "";
                }
                if (CommonFunctions::fileexists($label) && CommonFunctions::rfts($label, $buf, 1, 4096, false) && (trim($buf) != "")) {
                    $dev->setName(trim($buf).$name);
                } else {
                    $labelname = trim(preg_replace("/_input$/", "", pathinfo($sensor[$i], PATHINFO_BASENAME)));
                    if ($labelname !== "") {
                        $dev->setName($labelname.$name);
                    } else {
                        $dev->setName('unknown'.$name);
                    }
                }
                if (CommonFunctions::fileexists($fullmax) && CommonFunctions::rfts($fullmax, $buf, 1, 4096, false) && (trim($buf) != "")) {
                    $dev->setMax(trim($buf));
                } elseif (CommonFunctions::fileexists($max) && CommonFunctions::rfts($max, $buf, 1, 4096, false) && (trim($buf) != "")) {
                    $dev->setMax(trim($buf));
                }
                if (CommonFunctions::fileexists($min) && CommonFunctions::rfts($min, $buf, 1, 4096, false) && (trim($buf) != "")) {
                    $dev->setMin(trim($buf));
                }
                if (CommonFunctions::fileexists($alarm) && CommonFunctions::rfts($alarm, $buf, 1, 4096, false) && (trim($buf) === "1")) {
                    $dev->setEvent("Alarm");
                }
                $this->mbinfo->setMbFan($dev);
            }
        }
    }

    /**
     * get power information
     *
     * @param  string $hwpath
     * @return void
     */
    private function _power($hwpath)
    {
       $sensor = glob($hwpath."power*_input", GLOB_NOSORT);
       if (($total = count($sensor)) > 0) {
            $buf = "";
            for ($i = 0; $i < $total; $i++) if (CommonFunctions::rfts($sensor[$i], $buf, 1, 4096, false) && (trim($buf) != "")) {
                $dev = new SensorDevice();
                $dev->setValue(trim($buf)/1000000);
                $label = preg_replace("/_input$/", "_label", $sensor[$i]);
                $alarm = preg_replace("/_input$/", "_alarm", $sensor[$i]);
                $max = preg_replace("/_input$/", "_max", $sensor[$i]);
                $min = preg_replace("/_input$/", "_min", $sensor[$i]);
                $sname = preg_replace("/\/[^\/]*_input$/", "/name", $sensor[$i]);
                if (CommonFunctions::fileexists($sname) && CommonFunctions::rfts($sname, $buf, 1, 4096, false) && (trim($buf) != "")) {
                   $name = " (".trim($buf).")";
                } else {
                   $name = "";
                }
                if (CommonFunctions::fileexists($label) && CommonFunctions::rfts($label, $buf, 1, 4096, false) && (trim($buf) != "")) {
                    $dev->setName(trim($buf).$name);
                } else {
                    $labelname = trim(preg_replace("/_input$/", "", pathinfo($sensor[$i], PATHINFO_BASENAME)));
                    if ($labelname !== "") {
                        $dev->setName($labelname.$name);
                    } else {
                        $dev->setName('unknown'.$name);
                    }
                }
                if (CommonFunctions::fileexists($max) && CommonFunctions::rfts($max, $buf, 1, 4096, false) && (trim($buf) != "")) {
                    $dev->setMax(trim($buf)/1000000);
                }
                if (CommonFunctions::fileexists($min) && CommonFunctions::rfts($min, $buf, 1, 4096, false) && (trim($buf) != "")) {
                    $dev->setMin(trim($buf)/1000000);
                }
                if (CommonFunctions::fileexists($alarm) && CommonFunctions::rfts($alarm, $buf, 1, 4096, false) && (trim($buf) === "1")) {
                    $dev->setEvent("Alarm");
                }
                $this->mbinfo->setMbPower($dev);
            }
        }
    }

    /**
     * get current information
     *
     * @param  string $hwpath
     * @return void
     */
    private function _current($hwpath)
    {
       $sensor = glob($hwpath."curr*_input", GLOB_NOSORT);
       if (($total = count($sensor)) > 0) {
            $buf = "";
            for ($i = 0; $i < $total; $i++) if (CommonFunctions::rfts($sensor[$i], $buf, 1, 4096, false) && (trim($buf) != "")) {
                $dev = new SensorDevice();
                $dev->setValue(trim($buf)/1000);
                $label = preg_replace("/_input$/", "_label", $sensor[$i]);
                $alarm = preg_replace("/_input$/", "_alarm", $sensor[$i]);
                $max = preg_replace("/_input$/", "_max", $sensor[$i]);
                $min = preg_replace("/_input$/", "_min", $sensor[$i]);
                $sname = preg_replace("/\/[^\/]*_input$/", "/name", $sensor[$i]);
                if (CommonFunctions::fileexists($sname) && CommonFunctions::rfts($sname, $buf, 1, 4096, false) && (trim($buf) != "")) {
                   $name = " (".trim($buf).")";
                } else {
                   $name = "";
                }
                if (CommonFunctions::fileexists($label) && CommonFunctions::rfts($label, $buf, 1, 4096, false) && (trim($buf) != "")) {
                    $dev->setName(trim($buf).$name);
                } else {
                    $labelname = trim(preg_replace("/_input$/", "", pathinfo($sensor[$i], PATHINFO_BASENAME)));
                    if ($labelname !== "") {
                        $dev->setName($labelname.$name);
                    } else {
                        $dev->setName('unknown'.$name);
                    }
                }
                if (CommonFunctions::fileexists($max) && CommonFunctions::rfts($max, $buf, 1, 4096, false) && (trim($buf) != "")) {
                    $dev->setMax(trim($buf)/1000);
                }
                if (CommonFunctions::fileexists($min) && CommonFunctions::rfts($min, $buf, 1, 4096, false) && (trim($buf) != "")) {
                    $dev->setMin(trim($buf)/1000);
                }
                if (CommonFunctions::fileexists($alarm) && CommonFunctions::rfts($alarm, $buf, 1, 4096, false) && (trim($buf) === "1")) {
                    $dev->setEvent("Alarm");
                }
                $this->mbinfo->setMbCurrent($dev);
            }
        }
    }

    /**
     * get the information
     *
     * @see PSI_Interface_Sensor::build()
     *
     * @return Void
     */
    public function build()
    {
        $hwpaths = glob("/sys/class/hwmon/hwmon*/", GLOB_NOSORT);
        if (count($hwpaths) > 0) {
            $hwpaths = array_merge($hwpaths, glob("/sys/class/hwmon/hwmon*/device/", GLOB_NOSORT));
        }
        if (($totalh = count($hwpaths)) > 0) {
            for ($h = 0; $h < $totalh; $h++) {
                $this->_temperature($hwpaths[$h]);
                $this->_voltage($hwpaths[$h]);
                $this->_fans($hwpaths[$h]);
                $this->_power($hwpaths[$h]);
                $this->_current($hwpaths[$h]);
            }
        }
    }
}
