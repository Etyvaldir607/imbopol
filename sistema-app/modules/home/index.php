<?php require_once show_template('header-advanced'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Escritorio</strong>
	</h3>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-4 col-md-3">
			<div class="row margin-bottom">
				<div class="col-xs-10 col-xs-offset-1">
					<img src="<?= imgs . '/logo-color.png'; ?>" class="img-responsive">
				</div>
			</div>
			<div class="well text-center">
				<?php if ($_user['persona_id']) : ?>
				<h4 class="margin-none">Bienvenido al sistema!</h4>
				<p>
					<strong><?= escape($_user['nombres'] . ' ' . $_user['paterno'] . ' ' . $_user['materno']); ?></strong>
				</p>
				<?php else : ?>
				<h4 class="margin-none">Bienvenido al sistema!</h4>
				<p>
					<strong><?= escape($_user['username']); ?></strong>
				</p>
				<?php endif ?>
				<p>
					<img src="<?= ($_user['avatar'] == '') ? imgs . '/avatar.jpg' : profiles . '/' . $_user['avatar']; ?>" class="img-circle" width="128" height="128" data-toggle="modal" data-target="#modal_mostrar">
				</p>
				<p class="margin-none">
					<strong><?= escape($_user['email']); ?></strong>
					<br>
					<span class="text-success">en línea</span>
				</p>
			</div>
			<div class="list-group">
				<a href="?/home/perfil_ver" class="list-group-item">
					<span>Mostrar mi perfil</span>
					<span class="glyphicon glyphicon-menu-right pull-right"></span>
				</a>
				<a href="?/site/logout" class="list-group-item">
					<span>Cerrar mi sesión</span>
					<span class="glyphicon glyphicon-menu-right pull-right"></span>
				</a>
			</div>
		</div>
		<div class="col-sm-8 col-md-9">
			<div id="carousel" class="carousel slide margin-none">
				<ol class="carousel-indicators">
					<li data-target="#carousel" data-slide-to="0" class="active"></li>
					<li data-target="#carousel" data-slide-to="1"></li>
				</ol>
				<div class="carousel-inner">
					<div class="item active">
						<img src="<?= imgs; ?>/slides/slide-1.jpg" class="img-responsive">
						<div class="container">
							<div class="carousel-caption">
								<h3><?= escape($_institution['nombre']); ?></h3>
								<p><?= escape($_institution['lema']); ?></p>
							</div>
						</div>
					</div>
					<div class="item">
						<img src="<?= imgs; ?>/slides/slide-2.jpg" class="img-responsive">
						<div class="container">
							<div class="carousel-caption">
								<h3><?= escape($_institution['nombre']); ?></h3>
								<p><?= escape($_institution['lema']); ?></p>
							</div>
						</div>
					</div>
				</div>
				<a class="carousel-control left" href="#carousel" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span></a>
				<a class="carousel-control right" href="#carousel" data-slide="next"><span class="glyphicon glyphicon-chevron-right"></span></a>
			</div>
		</div>
	</div>
</div>
<?php require_once show_template('footer-advanced'); ?>