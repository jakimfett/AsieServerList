<div class="container">
    <div class="row" style="margin:15px 0;">
        <h1><?php echo $data->name; ?></h1>
        <p><?php echo $data->description; ?></p>
    </div>
    <div class="row">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="status">Status</th>
                    <th class="motd">Server</th>
                    <th>Players</th>
                </tr>
            </thead>
            <tbody>
                <?php $count = 0; ?>
                <?php foreach ($data->servers as $servername => $server): ?>
                    <?php $count++; ?>
                    <?php $stats = \Minecraft\Stats::retrieve(new \Minecraft\Server($server->ip)); ?>
                    <tr class="server-row" id="<?php echo $servername; ?>">
                        <td>
                            <?php if ($stats->is_online): ?>
                                <span class="badge badge-success"><i class="icon-ok icon-white"></i></span>
                            <?php else: ?>
                                <span class="badge badge-important"><i class="icon-remove icon-white"></i></span>
                            <?php endif; ?>
                        </td>
                        <td class="motd">
                            <?php echo $stats->motd; ?> <code><?php echo $server->ip; ?></code><span class="right"><a href="<?php echo $server->asie_ip; ?>launcher.jar">Get launcher</a></span>
                        </td>
                        <td>
                            <?php printf('%u/%u', $stats->online_players, $stats->max_players); ?>
                        </td>
                    </tr>
                    <?php if (isset($server->mods)) : ?>
                        <tr id="<?php echo $servername; ?>-mods" class="server-row hidden">
                            <td></td>
                            <td colspan="2">
                                <table>
                                    <tr>
                                        <td>

                                            <?php $count = 0; ?>
                                            <?php foreach ($server->mods as $value) : ?>
                                                <?php echo $value->name . ' version ' . $value->version . '<br/>'; ?>
                                                <?php $count++; ?>
                                                <?php if ($count >= 6): break;
                                                endif; ?>
                                            <?php endforeach; ?>
                                        </td>
                                        <td>

                                            <?php $count = 0; ?>
                                            <?php foreach ($server->plugins as $value) : ?>
                                                <?php echo $value->name . ' version ' . $value->version . '<br/>'; ?>
                                                <?php $count++; ?>
                                                <?php if ($count >= 6): break;
                                                endif; ?>
                                            <?php endforeach; ?>
                                        </td>
                                    </tr>
                                </table>

                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php unset($stats); ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
