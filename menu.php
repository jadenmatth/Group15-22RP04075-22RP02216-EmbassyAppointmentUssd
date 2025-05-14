<?php
require_once 'util.php';
require_once 'sms.php';

class Menu {
    protected $pdo;
    protected $sessionId;
    protected $phoneNumber;
    protected $text;
    protected $level;
    protected $userInput;
    protected $response;

    public function __construct() {
        $this->connectDatabase();
    }

    private function connectDatabase() {
        try {
            $this->pdo = new PDO(
                "mysql:host=" . Util::DB_HOST . ";dbname=" . Util::DB_NAME,
                Util::DB_USER,
                Util::DB_PASS
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function handleUSSDRequest($sessionId, $phoneNumber, $text) {
        $this->sessionId = $sessionId;
        $this->phoneNumber = $phoneNumber;
        $this->text = $text;
        
        if (empty($text)) {
            return $this->showMainMenu();
        }

        $textArray = explode("*", $text);
        $this->level = count($textArray);
        $this->userInput = end($textArray);

        switch ($this->level) {
            case 1:
                return $this->handleLevelOne();
            case 2:
                return $this->handleLevelTwo();
            case 3:
                return $this->handleLevelThree();
            case 4:
                return $this->handleLevelFour();
            case 5:
                return $this->handleLevelFive();
            case 6:
                return $this->handleLevelSix();
            case 7:
                return $this->handleLevelSeven();
            case 8:
                return $this->handleLevelEight();
            default:
                return $this->showMainMenu();
        }
    }

    private function showMainMenu() {
        return "CON Welcome to Embassy Appointment Service\n1. Schedule Appointment\n2. View/Cancel Appointment\n3. Reschedule Appointment\n4. Contact Embassy\n5. Exit";
    }

    private function handleLevelOne() {
        switch ($this->userInput) {
            case "1":
                return "CON Select Appointment Type:\n1. Visa Application\n2. Passport Services\n3. Return to Main Menu";
            case "2":
                return "CON Enter Appointment Reference Number:";
            case "3":
                return "CON Enter Appointment Reference Number:";
            case "4":
                return "END Embassy Contact Information:\nAddress: 123 Diplomatic Avenue, Capital City\nPhone: +1 234 567 8901\nEmail: consular@embassy.example\nOffice Hours: Mon-Fri, 8AM-4PM";
            case "5":
                return "END Thank you for using Embassy Appointment Service. Goodbye!";
            default:
                return "END Invalid input. Please try again.";
        }
    }

    private function handleLevelTwo() {
        $textArray = explode("*", $this->text);
        switch ($textArray[0]) {
            case "1":
                if ($this->userInput == "1") {
                    return "CON Select Visa Type:\n1. Tourist Visa\n2. Student Visa\n3. Work Visa\n4. Return to Previous Menu";
                }
                return "END Invalid selection. Please try again.";
            case "2":
            case "3":
                return $this->getAppointmentDetails($this->userInput);
            default:
                return "END Invalid input. Please try again.";
        }
    }

    private function handleLevelThree() {
        $textArray = explode("*", $this->text);
        if ($textArray[0] == "1" && $textArray[1] == "1") {
            switch ($this->userInput) {
                case "1":
                    return "CON Tourist Visa Appointment\n1. Required Documents\n2. Book Now\n3. Return to Previous Menu";
                case "2":
                    return "CON Student Visa Appointment\n1. Required Documents\n2. Book Now\n3. Return to Previous Menu";
                case "3":
                    return "CON Work Visa Appointment\n1. Required Documents\n2. Book Now\n3. Return to Previous Menu";
                case "4":
                    return $this->showMainMenu();
                default:
                    return "END Invalid selection. Please try again.";
            }
        }
        return "END Invalid input. Please try again.";
    }

    private function handleLevelFour() {
        $textArray = explode("*", $this->text);
        if ($textArray[0] == "1" && $textArray[1] == "1") {
            $visaType = $textArray[2];
            switch ($this->userInput) {
                case "1":
                    return $this->getRequiredDocuments($visaType);
                case "2":
                    return $this->getAvailableDates();
                case "3":
                    return "CON Select Visa Type:\n1. Tourist Visa\n2. Student Visa\n3. Work Visa\n4. Return to Previous Menu";
                default:
                    return "END Invalid selection. Please try again.";
            }
        }
        return "END Invalid input. Please try again.";
    }

    private function getRequiredDocuments($visaType) {
        $commonDocs = "1. Passport (valid 6+ months)\n2. Completed Application Form\n3. Passport Photo (2x2 inches)\n";
        
        switch ($visaType) {
            case "1": // Tourist Visa
                return "CON Required Documents:\n" . $commonDocs . 
                       "4. Proof of Travel Plans\n5. Financial Evidence\n6. Return to Previous Menu";
            
            case "2": // Student Visa
                return "CON Required Documents:\n" . $commonDocs . 
                       "4. Acceptance Letter\n5. Academic Records\n6. Financial Evidence\n" .
                       "7. Health Insurance\n8. Return to Previous Menu";
            
            case "3": // Work Visa
                return "CON Required Documents:\n" . $commonDocs . 
                       "4. Employment Contract\n5. Work Permit\n6. Professional Qualifications\n" .
                       "7. Financial Evidence\n8. Return to Previous Menu";
            
            default:
                return "END Invalid visa type selected.";
        }
    }

    private function handleLevelFive() {
        $textArray = explode("*", $this->text);
        if ($this->isBookingFlow($textArray)) {
            if (is_numeric($this->userInput) && $this->userInput >= 1 && $this->userInput <= 4) {
                // Validate if the selected date exists
                $selectedDate = $this->getSelectedDate($this->userInput);
                if ($selectedDate) {
                    return "CON Enter your full name as in passport:";
                }
                return "END Invalid date selection. Please try again.";
            } else if ($this->userInput == "5") {
                // Handle show more dates option
                return $this->getAvailableDates(true); // Add a parameter to show next set of dates
            } else if ($this->userInput == "6") {
                return "END Booking cancelled. Thank you for using our service.";
            }
            return "END Invalid date selection. Please try again.";
        }
        return "END Invalid input. Please try again.";
    }

    private function handleLevelSix() {
        $textArray = explode("*", $this->text);
        if ($this->isBookingFlow($textArray)) {
            return "CON Enter passport number:";
        }
        return "END Invalid input. Please try again.";
    }

    private function handleLevelSeven() {
        $textArray = explode("*", $this->text);
        if ($this->isBookingFlow($textArray)) {
            $name = $textArray[5];
            $passport = $this->userInput;
            $selectedDate = $this->getSelectedDate($textArray[4]);
            
            return "CON Confirm Booking:\nName: $name\nPassport: $passport\nDate: $selectedDate\n\n1. Confirm\n2. Edit Details\n3. Cancel";
        }
        return "END Invalid input. Please try again.";
    }

    private function handleLevelEight() {
        $textArray = explode("*", $this->text);
        if ($this->isBookingFlow($textArray) && $this->userInput == "1") {
            $name = $textArray[5];
            $passport = $textArray[6];
            $selectedDate = $this->getSelectedDate($textArray[4]);
            $refNumber = $this->generateRefNumber();
            
            $this->saveAppointment($refNumber, $name, $passport, $selectedDate);
            
            // Send SMS confirmation
            $sms = new Sms($this->phoneNumber);
            $message = "Your Embassy appointment is confirmed!\nRef: $refNumber\nDate: $selectedDate\nLocation: Embassy Main Building";
            $sms->sendSMS($message, $this->phoneNumber);
            
            return "END Booking Confirmed!\nRef: $refNumber\nDate: $selectedDate\nLocation: Embassy Main Building";
        }
        return "END Booking cancelled. Thank you for using our service.";
    }

    private function getAvailableDates($showMore = false) {
        $dates = $this->getNextAvailableDates();
        if (empty($dates)) {
            return "END No available dates found. Please try again later.";
        }

        $response = "CON Select Available Date:\n";
        foreach ($dates as $index => $date) {
            $response .= ($index + 1) . ". " . $date['formatted_date'] . "\n";
        }
        $response .= "5. Show More Dates\n6. Cancel Booking";
        return $response;
    }

    private function getNextAvailableDates() {
        $dates = [];
        $count = 0;
        $currentDate = new DateTime();
        $maxDays = 30; // Maximum days to look ahead
        $daysChecked = 0;
        
        while ($count < 4 && $daysChecked < $maxDays) {
            $currentDate->modify('+1 day');
            $daysChecked++;
            
            // Skip weekends
            if ($currentDate->format('N') >= 6) continue;
            
            $appointmentsCount = $this->getAppointmentsCountForDate($currentDate->format('Y-m-d'));
            if ($appointmentsCount < Util::MAX_APPOINTMENTS_PER_DAY) {
                $timeSlots = ['10:00 AM', '11:30 AM', '02:00 PM', '03:30 PM'];
                $dates[] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'formatted_date' => $currentDate->format('F j, Y') . ' (' . $timeSlots[array_rand($timeSlots)] . ')'
                ];
                $count++;
            }
        }
        return $dates;
    }

    private function getAppointmentsCountForDate($date) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM appointments WHERE date = ? AND status != ?");
        $stmt->execute([$date, Util::STATUS_CANCELLED]);
        return $stmt->fetchColumn();
    }

    private function getSelectedDate($selection) {
        $dates = $this->getNextAvailableDates();
        $index = $selection - 1;
        return isset($dates[$index]) ? $dates[$index]['formatted_date'] : null;
    }

    private function generateRefNumber() {
        return 'EMB' . date('Ymd') . rand(1000, 9999);
    }

    private function saveAppointment($refNumber, $name, $passport, $date) {
        $stmt = $this->pdo->prepare("INSERT INTO appointments (ref_number, phone, name, passport, date, time, type, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $visaTypes = ['1' => 'Tourist Visa', '2' => 'Student Visa', '3' => 'Work Visa'];
        $textArray = explode("*", $this->text);
        $visaType = $visaTypes[$textArray[2]] ?? 'Tourist Visa';
        
        $stmt->execute([
            $refNumber,
            $this->phoneNumber,
            $name,
            $passport,
            date('Y-m-d', strtotime($date)),
            '10:00 AM',
            $visaType,
            Util::STATUS_CONFIRMED
        ]);
    }

    private function getAppointmentDetails($refNumber) {
        $stmt = $this->pdo->prepare("SELECT * FROM appointments WHERE ref_number = ?");
        $stmt->execute([$refNumber]);
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$appointment) {
            return "END Appointment not found.";
        }

        return "CON Appointment Details:\nRef: " . $appointment['ref_number'] . 
               "\nType: " . $appointment['type'] . 
               "\nDate: " . $appointment['date'] . 
               "\nTime: " . $appointment['time'] . 
               "\nStatus: " . $appointment['status'] . 
               "\n\n1. Cancel Appointment\n2. Print Details\n3. Main Menu";
    }

    private function isBookingFlow($textArray) {
        return count($textArray) >= 4 && 
               $textArray[0] == "1" && 
               $textArray[1] == "1" && 
               in_array($textArray[2], ["1", "2", "3"]) && 
               $textArray[3] == "2";
    }
}
?>
