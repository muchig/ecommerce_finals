<?php
	include 'includes/session.php';
	
	function generateId() {
        // Generate a random 12-digit number
        $id = mt_rand(100000000000, 999999999999);

        // Check if the ID already exists in the database
        // and generate a new ID if it does
        while (idExistsInDatabase($id)) {
            $id = mt_rand(100000000000, 999999999999);
        }

        return $id;
    }

    function idExistsInDatabase($id) {

        return false;
    }

    if(isset($_GET['pay'])){
        $payid = generateId();
        $date = date('Y-m-d');

        $conn = $pdo->open();

		try{
			
			$stmt = $conn->prepare("INSERT INTO sales (user_id, pay_id, sales_date) VALUES (:user_id, :pay_id, :sales_date)");
			$stmt->execute(['user_id'=>$user['id'], 'pay_id'=>$payid, 'sales_date'=>$date]);
			$salesid = $conn->lastInsertId();
			
			try{
				$stmt = $conn->prepare("SELECT * FROM cart LEFT JOIN products ON products.id=cart.product_id WHERE user_id=:user_id");
				$stmt->execute(['user_id'=>$user['id']]);

				foreach($stmt as $row){
					$stmt = $conn->prepare("INSERT INTO details (sales_id, product_id, quantity) VALUES (:sales_id, :product_id, :quantity)");
					$stmt->execute(['sales_id'=>$salesid, 'product_id'=>$row['product_id'], 'quantity'=>$row['quantity']]);
				}

				$stmt = $conn->prepare("DELETE FROM cart WHERE user_id=:user_id");
				$stmt->execute(['user_id'=>$user['id']]);

				$_SESSION['success'] = 'Transaction successful. Thank you.';

			}
			catch(PDOException $e){
				$_SESSION['error'] = $e->getMessage();
			}

		}
		catch(PDOException $e){
			$_SESSION['error'] = $e->getMessage();
		}

		$pdo->close();
	}
	
	header('location: profile.php');
	
?>