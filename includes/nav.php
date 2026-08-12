<?php
$current = basename($_SERVER['PHP_SELF']);
?>

<nav class="profile-nav">
    <a href="dashboard.php" class="<?php echo $current === 'dashboard.php' ? 'active' : ''; ?>">
        <?php if ($current === 'dashboard.php'): ?><i class="ti ti-home"></i> <?php endif; ?>Dashboard
    </a>
    <a href="workout_log.php" class="<?php echo $current === 'workout_log.php' ? 'active' : ''; ?>">
        <?php if ($current === 'workout_log.php'): ?><i class="ti ti-barbell"></i> <?php endif; ?>Workouts
    </a>
    <a href="exercises.php" class="<?php echo $current === 'exercises.php' ? 'active' : ''; ?>">
        <?php if ($current === 'exercises.php'): ?><i class="ti ti-barbell"></i> <?php endif; ?>Übungen
    </a>
    <a href="goals.php" class="<?php echo $current === 'goals.php' ? 'active' : ''; ?>">
        <?php if ($current === 'goals.php'): ?><i class="ti ti-target"></i> <?php endif; ?>Ziele
    </a>
    <a href="profile.php" class="<?php echo $current === 'profile.php' ? 'active' : ''; ?>">
        <?php if ($current === 'profile.php'): ?><i class="ti ti-user"></i> <?php endif; ?>Profil
    </a>
    <a href="../api/logout.php" class="nav-logout">
        <i class="ti ti-logout"></i> Abmelden
    </a>
</nav>