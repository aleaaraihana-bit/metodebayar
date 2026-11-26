```php
<?php
require_once 'functions.php';
batasiAksesPelanggan();

$notif_sukses = "";
$notif_error  = "";

// TAMBAH PENGGUNA
if (isset($_POST['tambah_user'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $nama     = mysqli_real_escape_string($conn, trim($_POST['nama_lengkap']));
    $role     = mysqli_real_escape_string($conn, $_POST['role']);
    $password = $_POST['password'];

    // Role yang diperbolehkan
    $role_valid = ['Admin', 'Kasir', 'Pelanggan'];

    if (!in_array($role, $role_valid, true)) {
        $notif_error = "Role pengguna tidak valid!";
    } elseif (empty($username) || empty($nama) || empty($password)) {
        $notif_error = "Semua kolom wajib diisi!";
    } else {
        $pass = password_hash($password, PASSWORD_DEFAULT);

        $query = mysqli_query($conn, "
            INSERT INTO tbl_users
            (username, password, nama_lengkap, role)
            VALUES ('$username', '$pass', '$nama', '$role')
        ");

        if ($query) {
            $notif_sukses = "Akun pengguna '$username' berhasil ditambahkan!";
        } else {
            $notif_error = "Gagal menambahkan pengguna. Username mungkin sudah terdaftar.";
        }
    }
}

// HAPUS PENGGUNA
if (isset($_GET['hapus'])) {
    $id_u = (int) $_GET['hapus'];

    if ($id_u === 1) {
        $notif_error = "Akun Admin utama tidak boleh dihapus!";
    } else {
        $hapus = mysqli_query($conn, "DELETE FROM tbl_users WHERE id_user = $id_u");

        if ($hapus && mysqli_affected_rows($conn) > 0) {
            $notif_sukses = "Akun pengguna berhasil dihapus!";
        } else {
            $notif_error = "Pengguna gagal dihapus atau tidak ditemukan.";
        }
    }
}

// AMBIL DATA PENGGUNA
$q_users = mysqli_query($conn, "
    SELECT * FROM tbl_users
    ORDER BY id_user ASC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - TokoKita</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-slate-100 text-slate-800 font-sans min-h-screen">

    <?php include 'navbar.php'; ?>

    <main class="max-w-7xl mx-auto p-6 space-y-6">

        <div>
            <h1 class="text-2xl font-bold text-slate-800">Manajemen Pengguna</h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola akun Admin, Kasir, dan Pelanggan TokoKita.
            </p>
        </div>

        <?php if ($notif_sukses): ?>
            <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-2xl text-sm font-medium">
                <?= htmlspecialchars($notif_sukses) ?>
            </div>
        <?php endif; ?>

        <?php if ($notif_error): ?>
            <div class="bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-2xl text-sm font-medium">
                <?= htmlspecialchars($notif_error) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- FORM TAMBAH PENGGUNA -->
            <form method="POST" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">

                <h2 class="font-bold text-base flex items-center gap-2 border-b pb-3">
                    <i data-lucide="shield-plus" class="w-5 h-5 text-indigo-600"></i>
                    Tambah Pengguna
                </h2>

                <div>
                    <label class="block text-sm font-medium mb-1">Nama Lengkap</label>
                    <input
                        type="text"
                        name="nama_lengkap"
                        placeholder="Masukkan nama lengkap"
                        required
                        class="w-full border p-2.5 rounded-xl text-sm"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Username</label>
                    <input
                        type="text"
                        name="username"
                        placeholder="Username login"
                        required
                        class="w-full border p-2.5 rounded-xl text-sm"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Password</label>
                    <input
                        type="password"
                        name="password"
                        placeholder="Masukkan password"
                        required
                        class="w-full border p-2.5 rounded-xl text-sm"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Hak Akses (Role)</label>
                    <select
                        name="role"
                        required
                        class="w-full border p-2.5 rounded-xl text-sm"
                    >
                        <option value="">-- Pilih Role --</option>
                        <option value="Admin">Admin</option>
                        <option value="Kasir">Kasir</option>
                        <option value="Pelanggan">Pelanggan</option>
                    </select>
                </div>

                <button
                    type="submit"
                    name="tambah_user"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 rounded-xl transition"
                >
                    <i data-lucide="user-plus" class="w-4 h-4 inline-block mr-1"></i>
                    Simpan Pengguna Baru
                </button>

            </form>

            <!-- DAFTAR PENGGUNA -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">

                <h2 class="font-bold text-base mb-4">
                    Daftar Pengguna Aktif (tbl_users)
                </h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse">

                        <thead>
                            <tr class="bg-slate-100 text-left text-xs text-slate-600">
                                <th class="p-3">ID</th>
                                <th class="p-3">Nama Lengkap</th>
                                <th class="p-3">Username</th>
                                <th class="p-3">Hak Akses</th>
                                <th class="p-3 text-right">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if ($q_users && mysqli_num_rows($q_users) > 0): ?>

                                <?php while ($u = mysqli_fetch_assoc($q_users)): ?>
                                    <tr class="border-b hover:bg-slate-50">

                                        <td class="p-3">
                                            #<?= (int)$u['id_user'] ?>
                                        </td>

                                        <td class="p-3 font-semibold">
                                            <?= htmlspecialchars($u['nama_lengkap']) ?>
                                        </td>

                                        <td class="p-3 font-mono text-xs text-indigo-600">
                                            <?= htmlspecialchars($u['username']) ?>
                                        </td>

                                        <td class="p-3">
                                            <?php
                                            $role_user = $u['role'];
                                            $warna = 'bg-slate-100 text-slate-700';

                                            if (in_array(strtolower($role_user), ['admin', 'super admin'])) {
                                                $warna = 'bg-purple-100 text-purple-800';
                                            } elseif (in_array(strtolower($role_user), ['kasir', 'kasir pos'])) {
                                                $warna = 'bg-blue-100 text-blue-800';
                                            } elseif (in_array(strtolower($role_user), ['pelanggan', 'customer', 'user'])) {
                                                $warna = 'bg-emerald-100 text-emerald-800';
                                            }
                                            ?>

                                            <span class="<?= $warna ?> px-2.5 py-1 rounded-full text-xs font-bold">
                                                <?= htmlspecialchars($role_user) ?>
                                            </span>
                                        </td>

                                        <td class="p-3 text-right">
                                            <?php if ((int)$u['id_user'] !== 1): ?>
                                                <a
                                                    href="?hapus=<?= (int)$u['id_user'] ?>"
                                                    onclick="return confirm('Yakin ingin menghapus pengguna ini?')"
                                                    class="text-rose-600 hover:underline text-xs font-semibold"
                                                >
                                                    Hapus
                                                </a>
                                            <?php else: ?>
                                                <span class="text-xs text-slate-400">Utama</span>
                                            <?php endif; ?>
                                        </td>

                                    </tr>
                                <?php endwhile; ?>

                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-slate-400">
                                        Belum ada data pengguna.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>

            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>

</body>
</html>