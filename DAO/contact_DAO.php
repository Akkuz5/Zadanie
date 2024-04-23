<?php
require_once '../models/contact.php';

class ContactDAO {
    private $filePath = '../contacts.json';

    private function readContacts() {
        $json = file_get_contents($this->filePath);
        $data = json_decode($json, true);
        return $data['contacts'] ?? [];
    }

    private function saveContacts($contacts) {
        $data = ['contacts' => $contacts];
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($this->filePath, $json);
    }
    
    public function addContact(Contact $contact) {
        $contacts = $this->readContacts();
        $newId = count($contacts) > 0 ? max(array_column($contacts, 'id')) + 1 : 1;
        $contact->setId($newId); 
        $contacts[] = [
            'id' => $contact->getId(),
            'name' => $contact->getName(),
            'phone_number' => $contact->getPhoneNumber()
        ];
        $this->saveContacts($contacts);
    }
    

    public function getContactById($id) {
        $contacts = $this->readContacts();
        foreach ($contacts as $contact) {
            if ($contact['id'] == $id) {
                return new Contact($contact['id'], $contact['name'], $contact['phone_number']);
            }
        }
        return null;
    }

    public function updateContact(Contact $contact) {
        $contacts = $this->readContacts();
        foreach ($contacts as $index => $oldContact) {
            if ($oldContact['id'] == $contact->getId()) {
                $contacts[$index] = [
                    'id' => $contact->getId(),
                    'name' => $contact->getName(),
                    'phone_number' => $contact->getPhoneNumber()
                ];
                $this->saveContacts($contacts);
                return true;
            }
        }
        return false;
    }

    public function deleteContact($id) {
        $contacts = $this->readContacts();
        foreach ($contacts as $index => $contact) {
            if ($contact['id'] == $id) {
                array_splice($contacts, $index, 1);
                $this->saveContacts($contacts);
                return true;
            }
        }
        return false;
    }

    public function getAllContacts() {
        $contacts = $this->readContacts();
        return array_map(function ($contact) {
            return new Contact($contact['id'], $contact['name'], $contact['phone_number']);
        }, $contacts);
    }

    public function getAllContactsJson() {  
        $contacts = $this->getAllContacts();
        $contactArray = array_map(function($contact) {
            return [
                'id' => $contact->getId(),
                'name' => $contact->getName(),
                'phone_number' => $contact->getPhoneNumber(),
            ];
        }, $contacts);
    
        return json_encode($contactArray); 
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getAllContactsJson') {
    $dao = new ContactDAO();
    echo $dao->getAllContactsJson();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'deleteMultipleContacts' && !empty($_POST['ids'])) {
    $dao = new ContactDAO(); 
    $ids = explode(',', $_POST['ids']);
    $errors = [];
    $successCount = 0;

    foreach ($ids as $id) {
        $success = $dao->deleteContact(trim($id));

        if ($success) {
            $successCount++;
        } else {
            $errors[] = ['id' => $id, 'message' => 'Не удалось удалить контакт.'];
        }
    }

    if ($successCount > 0 && count($errors) === 0) {
        echo json_encode(['success' => true, 'message' => 'Все выбранные контакты успешно удалены.']);
    } else {
        echo json_encode(['success' => false, 'errors' => $errors]);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getContactById' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $dao = new ContactDAO();
    $contact = $dao->getContactById($id);

    if ($contact) {
        echo json_encode([
            'id' => $contact->getId(),
            'name' => $contact->getName(),
            'phone_number' => $contact->getPhoneNumber(),
        ]);
    } else {
        echo json_encode(['error' => 'Контакт не найден']);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $dao = new ContactDAO();
    switch ($_POST['action']) {
        case 'addContact':
            $id = $_POST['id'];
            $name = $_POST['name'];
            $phone_number = $_POST['phone_number'];

            $contact = new Contact($id, $name, $phone_number);
            $dao->addContact($contact);

            echo json_encode(['success' => true, 'message' => 'Контакт добавлен']);
            break;
        case 'updateContact':
            $id = $_POST['id'];
            $name = $_POST['name'];
            $phone_number = $_POST['phone_number'];

            $contact = new Contact($id, $name, $phone_number);
            $success = $dao->updateContact($contact);

            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Контакт обновлен']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении контакта']);
            }
            break;
    }
    exit();
}
?>