<div class="container">
    <div class="row" style="margin:15px 0;">
        <h1><img src="AsiePlatformLogo.png"/> AsiePlatform Serverlist</h1>
    </div>
    <div class="row">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="status">Status</th>
                    <th class="motd">Server</th>
                    <th>Players</th>
                    <th>More</th>
                </tr>
            </thead>
            <tbody>
                <?php $count = 0; ?>
                <?php foreach ($servers as $servername => $server): ?>
                    <?php $count++; ?>
                    <?php $stats = \Minecraft\Stats::retrieve(new \Minecraft\Server($server['ip'])); ?>
                    <tr class="server-row" id="<?php echo $servername; ?>">
                        <td>
                            <?php if ($stats->is_online): ?>
                                <span class="badge badge-success"><i class="icon-ok icon-white"></i></span>
                            <?php else: ?>
                                <span class="badge badge-important"><i class="icon-remove icon-white"></i></span>
                            <?php endif; ?>
                        </td>
                        <td class="motd">
                            <?php echo $stats->motd; ?> <code><?php echo $server['ip']; ?></code><span class="right"><a href="<?php echo $server['asie_url']; ?>launcher.jar">Get launcher</a></span>
                        </td>
                        <td>
                            <?php printf('%u/%u', $stats->online_players, $stats->max_players); ?>
                        </td>
                        <td>
                            <span class="badge badge-success"><i class="icon-plus icon-white"></i></span>
                        </td>
                    </tr>
                    <?php if (isset($server['mods']) || isset($server['plugins'])) : ?>
                        <tr id="<?php echo $servername; ?>-mods" class="server-row hidden">
                            <td></td>
                            <td colspan="2">
                                <table>
                                    <tr>
                                        <td>
                                            <?php if (isset($server['mods'])) : ?>
                                                <?php $count = 0; ?>
                                                <?php foreach ($server['mods'] as $name => $version) : ?>
                                                    <?php echo $name . ' version ' . $version . '<br/>'; ?>
                                                    <?php $count++; ?>
                                                    <?php if ($count >= 6): break;
                                                    endif;
                                                    ?>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                (No mods found for this server)
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (isset($server['plugins'])) : ?>
                                                <?php $count = 0; ?>
                                                <?php foreach ($server['plugins'] as $name => $version) : ?>
                                                    <?php echo $name . ' version ' . $version . '<br/>'; ?>
                                                    <?php $count++; ?>
                                                    <?php if ($count >= 6): break;
                                                    endif;
                                                    ?>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                (No plugins found for this server)
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            (playernames list will go here)
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
