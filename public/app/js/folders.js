

$(function () {
  const headers = {
    "Content-Type": "application/json",
    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
  };

  const fileManager = $("#file-manager").dxFileManager({
    name: "fileManager",
    fileProvider: [],
    height: 450,
    width: 800,
    itemView: { mode: "thumbnails" },
    permissions: {
      create: false,
      rename: true
    },
    contextMenu: {
      items: [
        // Specify a predefined item's name only
        {
          name: "rename-folder", // Custom delete button
          icon: "",
          html: "<i class='dx-icon dx-icon-rename'></i><span class='dx-button-rename'>rename</span>",
          visible: true,
          onClick: function (e) {
            renameFolder(e);
          }
        },
        {
          name: "delete-folder", // Custom delete button
          icon: "",
          html: "<i class='dx-icon dx-icon-trash'></i><span class='dx-button-text'>Delete</span>",
          visible: true,
          onClick: function () {
            customDeleteHandler();
          },
        }
      ]
    },
    toolbar: {
      items: [
        { name: "upload", visible: true },
        {
          name: "refresh-folder",
          options: {
            text: "Refresh",  // Add a text label
            icon: "refresh",
            position: "left" // If your UI framework supports positioning

          },
          visible: true,
          onClick: function () {
            fetchFileManagerData();
          }
        },
        {
          name: "create-folder",
          options: {
            text: "New Folder",
            icon: "plus"
          },
          visible: createFolderPermission,
          onClick: function () {
            createNewFolder();
          }
        }

      ],
      fileSelectionItems: [

        {
          name: "delete-folder",
          options: {
            text: "Delete",
            icon: "trash"
          },
          visible: true,
          onClick: function () {
            customDeleteHandler();
          }
        },
        'clearSelection'
      ]
    },
    onSelectionChanged: function (e) {
      let selectedItems = e.selectedItems;
      let canDelete = selectedItems.length > 0 && selectedItems.every(item => item.dataItem.permissions && item.dataItem.permissions.delete && deleteFolderPermission);
      fileManager.option("contextMenu.items[0].visible", selectedItems.length > 1 ? false : true)
      fileManager.option("toolbar.fileSelectionItems[0].visible", canDelete)
      fileManager.option("contextMenu.items[1].visible", canDelete)
    },
    onItemContextMenu: function (e) {
      let selectedItems = e.selectedItems;
      console.log(selectedItems);
    }
  }).dxFileManager("instance");


  function renameFolder(e) {

    let selectedItem = fileManager.getSelectedItems()[0]; // Get the selected item
    $('#folderModalLabel').text('Rename Folder');
    let updateUrl = createFolderRoute + "/" + selectedItem.dataItem.id;
    $.ajax({
      url: updateUrl,
      type: 'GET',

      success: function (response) {
        $('#role-select-edit').val(response.roles).change()
        $('#folderName').val(response.name);

        $('#folderModal').modal('show');
      },
      error: function (xhr) {
        alert('Error: ' + (xhr.responseJSON.message ||
          'An unexpected error occurred.'));
      }
    });
  }

  $('#folderForm').on('submit', function (e) {
    e.preventDefault();
    let selectedItem = fileManager.getSelectedItems()[0]; // Get the selected item
    let editUrl = createFolderRoute + "/" + selectedItem.dataItem.id;

    $.ajax({
      url: editUrl,
      type: 'PUT',
      data: $(this).serialize(),
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
          'content') // Include CSRF token
      },
      success: function (response) {
        if (response.success) {
          $('#folderModal').modal('hide');
          fetchFileManagerData();
        }
        alert(response.message);

      },
      error: function (xhr) {
        alert('Error: ' + (xhr.responseJSON.message ||
          'An unexpected error occurred.'));
      }
    });
  });

  //delete handle
  function customDeleteHandler() {
    let selectedItems = fileManager.getSelectedItems();
    if (selectedItems.length === 0) {
      alert("No items selected.");
      return;
    }

    let selectedIds = selectedItems.map(item => item.dataItem.id);

    $.ajax({
      url: deleteFolderRoute, // Ensure this is correctly defined
      type: "POST",  // Change to "DELETE" if your backend expects DELETE
      data: JSON.stringify({ folder_ids: selectedIds }), // Convert to JSON string
      headers: {
        "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"), // Include CSRF token if needed
        "Content-Type": "application/json" // Ensure correct content type
      },
      success: function (response) {
        if (response.success) {
          fetchFileManagerData(); // Refresh file manager
          alert(response.message); // Show error message
        }
      },
      error: function (xhr, status, error) {
        console.error("Error:", xhr.responseText); // Log error response
        alert("An unexpected error occurred. Please try again.");
      }
    });
  }

  // ✅ Fetch File Manager Data
  async function fetchFileManagerData() {
    try {
      const response = await fetch(getFileMangerRoute);
      const data = await response.json();
      fileManager.option("fileSystemProvider", data);

    } catch (error) {
      console.error("Error fetching data:", error);
    }
  }


  // ✅ Custom Function to Create a New Folder
  function createNewFolder() {
    $('#createFolderForm')[0].reset();
    $('#role-select').change();
    $('#createFolderModal').modal('show');
  }


  // Handle folder creation
  $('#createFolderForm').on('submit', function (e) {
    e.preventDefault();

    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const currentDir = fileManager.getCurrentDirectory();
    const parentId = currentDir.dataItem?.id || "";

    let formData = new FormData(this);
    formData.append("parent_id", parentId); // Append parent_id

    $.ajax({
      url: createFolderRoute, // Ensure this route is correctly defined
      type: "POST",
      data: formData,
      headers: {
        "X-CSRF-TOKEN": csrfToken // ✅ CSRF token included
      },
      processData: false,  // ✅ Prevent jQuery from processing data
      contentType: false,  // ✅ Prevent jQuery from setting Content-Type
      success: function (response) {
        if (response.success) {
          $('#createFolderModal').modal('hide'); // Close modal
          fetchFileManagerData(); // Refresh file manager
        } else {
          alert(response.message); // Show error message
        }
      },
      error: function (xhr, status, error) {
        console.error("Error:", xhr.responseText); // Log error response
        alert("An unexpected error occurred. Please try again.");
      }
    });
  });





  // Initialize File Manager Data
  fetchFileManagerData();
});
