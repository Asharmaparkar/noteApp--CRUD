<?php
class Database
{
    private $servername = "localhost";
    private $username = "root";
    private $password = "Thakur@301";
    private $database = "notes";
    private $conn;

    private function __construct()
    {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->database);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public static function getInstance()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    public function getConnection()
    {
        return $this->conn;
    }
}


class NotesManager
{
  private $insert = false;
  private $update = false;
  private $delete = false;
  private $db;

  public function __construct(Database $db)
  {
    $this->db = $db;
  }

  public function handleDeleteRequest()
  {
    if (isset($_GET['delete'])) {
      $sno = $_GET['delete'];
      $this->delete = true;
      $sql = "DELETE FROM `notes` WHERE `sno` = $sno";
      $result = mysqli_query($this->db->getConnection(), $sql);
    }
  }

  public function handlePostRequest()
  {
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
      if (isset($_POST['snoEdit'])) {
        $sno = $_POST["snoEdit"];
        $title = $_POST["titleEdit"];
        $description = $_POST["descriptionEdit"];

        $sql = "UPDATE `notes` SET `title` = '$title', `description` = '$description' WHERE `sno` = $sno";
        $result = mysqli_query($this->db->getConnection(), $sql);

        if ($result) {
          $this->update = true;
        } else {
          echo "We could not update the record successfully";
        }
      } else {
        $title = $_POST["title"];
        $description = $_POST["description"];

        $sql = "INSERT INTO `notes`(`title`,`description`) VALUES('$title','$description')";
        $result = mysqli_query($this->db->getConnection(), $sql);

        if ($result) {
          $this->insert = true;
        } else {
          echo "The record was not inserted because of these error -->" . mysqli_error($this->db->getConnection());
        }
      }
    }
  }

  public function displayAlerts()
  {
    if ($this->insert) {
      echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <strong>Success!</strong> Your note has been inserted successfully.
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
    }
    if ($this->update) {
      echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <strong>Success!</strong> Your note has been Updated successfully.
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
    }
    if ($this->delete) {
      echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <strong>Success!</strong> Your note has been Deleted successfully.
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
    }
  }

  public function displayNotesTable()
  {
    $sql = "SELECT * FROM notes";
    $result = mysqli_query($this->db->getConnection(), $sql);
    $sno = 0;

    echo '<div class="container my-4">';
    echo '<table class="table" id="myTable">';
    echo '<thead>';
    echo '<tr>
              <th scope="col">S.No</th>
              <th scope="col">Title</th>
              <th scope="col">Description</th>
              <th scope="col">Actions</th>
            </tr>';
    echo '</thead>';
    echo '<tbody>';
    //   echo '<tr id="noDataFoundRow" style="display: none">
    //   <td colspan="4">No data found</td>
    // </tr>';

    while ($row = mysqli_fetch_assoc($result)) {
      $sno = $sno + 1;
      echo "<tr>
                <th scope='row' class='sno-column'>" . $sno . "</th>
                <td>" . $row['title'] . "</td>
                <td>" . $row['description'] . "</td>
                <td> <button class='edit btn btn-sm btn-primary' id=" . $row['sno'] . ">Edit</button> <button class='delete btn btn-sm btn-danger' id=d" . $row['sno'] . ">Delete</button></td>
            </tr>";
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <title>Note app - CRUD</title>
</head>

<body>
  <!-- Modal -->
  <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="editModalLabel">Update Your Note</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="/CRUD/index.php" method="post">
            <input type="hidden" name="snoEdit" id="snoEdit" />
            <div class="mb-3">
              <label for="title" class="form-label">Title</label>
              <input type="text" class="form-control" id="titleEdit" name="titleEdit" aria-describedby="emailHelp">
            </div>
            <div class="form-group">
              <label for="description" class="form-label">Description</label>
              <textarea class="form-control" placeholder="Add your Note description" id="descriptionEdit" name="descriptionEdit" row="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary my-2">Update</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <?php
  $database = Database::getInstance();
  $notesManager = new NotesManager($database);
  $notesManager->handleDeleteRequest();
  $notesManager->handlePostRequest();
  ?>

  <nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-opacity-75">
    <div class="container-fluid">
      <a class="navbar-brand" href="/CRUD/index.php">PHP CRUD</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="/CRUD/index.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Contact us</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <?php
  $notesManager->displayAlerts();
  ?>

  <div class="container my-3">
    <h3>Add Notes</h3>
    <form action="/CRUD/index.php" method="post">
      <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" class="form-control" placeholder="Note title" id="title" name="title" aria-describedby="emailHelp">
      </div>
      <div class="form-group">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" placeholder="Note description" id="description" name="description" row="3"></textarea>
      </div>
      <button type="submit" class="btn btn-primary my-2">Add Note</button>
    </form>
  </div>

  <div class="container my-3">
    <h3>Search Notes</h3>
    <input type="text" id="searchInput" class="form-control" onkeyup="searchNotes()" placeholder="Search">
  </div>

  <div class="container my-4" id="notesTable" style="display: none;">
    <?php
    $notesManager->displayNotesTable();
    ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
  <script>
    edits = document.getElementsByClassName('edit');
    Array.from(edits).forEach((element) => {
      element.addEventListener("click", (e) => {
        tr = e.target.parentNode.parentNode;
        title = tr.getElementsByTagName("td")[0].innerText;
        description = tr.getElementsByTagName("td")[1].innerText;
        // alert(`You clicked ${title} which has the description: "${description}"`);
        descriptionEdit.value = description;
        titleEdit.value = title;
        snoEdit.value = e.target.id;
        $('#editModal').modal('toggle');
      })
    })

    deletes = document.getElementsByClassName('delete');
    Array.from(deletes).forEach((element) => {
      element.addEventListener("click", (e) => {
        sno = e.target.id.substr(1);

        if (confirm("Press the Button!!")) {
          console.log("yes");
          window.location = `/crud/index.php?delete=${sno}`;
        }
      })
    });
  </script>
  <script>
    function searchNotes() {
      var input, filter, table, tr, th, td, i, txtValue;
      input = document.getElementById("searchInput");
      filter = input.value.toUpperCase();
      table = document.getElementById("myTable");
      tr = table.getElementsByTagName("tr");

      document.getElementById("notesTable").style.display = "none";

      var matchFound = false;
      for (i = 0; i < tr.length; i++) {
        matchFound = false; 
        td = tr[i].getElementsByTagName("td");
        var snoColumn = tr[i].getElementsByClassName("sno-column")[0]; // Find the S.No column

        if (snoColumn) {
          txtValue = snoColumn.textContent || snoColumn.innerText;

          if (txtValue.trim() === filter) {
            matchFound = true;
          }
        }
        for (var j = 0; j < 4; j++) { // Loop through the three columns (title, sno, description)
          if (i === 0) {
            th = tr[i].getElementsByTagName("th")[j];
            if (th) {
              txtValue = th.textContent || th.innerText;
            }
          } else {
            // Handle data rows
            td = tr[i].getElementsByTagName("td")[j];
            if (td) {
              txtValue = td.textContent || td.innerText;
            }
          }
          if (txtValue.toUpperCase().indexOf(filter) > -1) {
            matchFound = true;
          }
        }
        if (i === 0) {
          tr[i].style.display = "";
        } else if (matchFound) {
          tr[i].style.display = "";
        } else {
          tr[i].style.display = "none";
        }
      }

      // Show the table only if there are matching results
      if (filter.length > 0) {
        document.getElementById("notesTable").style.display = "block";
      }

      // Hide table if no matches were found
      document.getElementById("noDataFoundRow").style.display = matchFound ? "none" : "table-row";
    }
  </script>
</body>

</html>
