/**
 * WebSocket service management module
 * Provides functions to start, stop, check status, and debug WebSocket (Laravel Echo Server) services
 */

import { exec } from 'child_process';
import { promisify } from 'util';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const execAsync = promisify(exec);
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '../../');

/**
 * Start the WebSocket server
 * @returns {Promise<string>} - Success or error message
 */
export async function startWebSocketServer() {
  try {
    const scriptPath = path.join(projectRoot, 'start-echo-server.sh');
    
    // Check if script exists
    if (!fs.existsSync(scriptPath)) {
      return 'Error: start-echo-server.sh script not found. Create the script first.';
    }
    
    // Make script executable
    await execAsync(`chmod +x ${scriptPath}`);
    
    // Execute in background
    const { stdout } = await execAsync(`${scriptPath} > ${projectRoot}/storage/logs/echo-server.log 2>&1 &`);
    return 'WebSocket server started successfully.';
  } catch (error) {
    return `Error starting WebSocket server: ${error.message}`;
  }
}

/**
 * Stop the WebSocket server
 * @returns {Promise<string>} - Success or error message
 */
export async function stopWebSocketServer() {
  try {
    const { stdout } = await execAsync("ps aux | grep 'laravel-echo-server' | grep -v grep | awk '{print $2}' | xargs -r kill");
    return 'WebSocket server stopped successfully.';
  } catch (error) {
    return `Error stopping WebSocket server: ${error.message}`;
  }
}

/**
 * Check the status of the WebSocket server
 * @returns {Promise<string>} - Status message
 */
export async function checkWebSocketStatus() {
  try {
    const { stdout } = await execAsync("ps aux | grep 'laravel-echo-server' | grep -v grep");
    
    if (stdout.trim()) {
      // Get the process info
      const processInfo = stdout.split('\n')[0];
      
      // Try to get the port info
      try {
        const { stdout: netstatOutput } = await execAsync("netstat -tlpn | grep laravel-echo-server");
        const portInfo = netstatOutput.includes(':') ? 
          `Port: ${netstatOutput.match(/:(\d+)/)[1]}` : 'Port unknown';
        
        return `WebSocket server is running.\n${processInfo}\n${portInfo}`;
      } catch (netstatError) {
        return `WebSocket server is running.\n${processInfo}`;
      }
    } else {
      return 'WebSocket server is not running.';
    }
  } catch (error) {
    return `Error checking WebSocket server status: ${error.message}`;
  }
}

/**
 * Debug the WebSocket server
 * @returns {Promise<string>} - Debug information
 */
export async function debugWebSocketServer() {
  try {
    // Check server status
    const status = await checkWebSocketStatus();
    
    // Check config file
    const configPath = path.join(projectRoot, 'laravel-echo-server.json');
    let configInfo = 'Config file not found.';
    
    if (fs.existsSync(configPath)) {
      try {
        const configContent = fs.readFileSync(configPath, 'utf8');
        const config = JSON.parse(configContent);
        configInfo = `Config file found:\nPort: ${config.port}\nProtocol: ${config.protocol}\nHost: ${config.host || 'default'}\nAuth Host: ${config.authHost}`;
      } catch (parseError) {
        configInfo = `Error parsing config file: ${parseError.message}`;
      }
    }
    
    // Check recent logs
    const logPath = path.join(projectRoot, 'storage/logs/echo-server.log');
    let recentLogs = 'Log file not found.';
    
    if (fs.existsSync(logPath)) {
      try {
        const { stdout } = await execAsync(`tail -n 20 ${logPath}`);
        recentLogs = `Recent logs:\n${stdout}`;
      } catch (logError) {
        recentLogs = `Error reading logs: ${logError.message}`;
      }
    }
    
    return `=== WebSocket Server Debug Info ===\n\n${status}\n\n${configInfo}\n\n${recentLogs}`;
  } catch (error) {
    return `Error debugging WebSocket server: ${error.message}`;
  }
}

/**
 * Main WebSocket command handler
 * @param {string[]} args - Command arguments
 * @returns {Promise<string>} - Result of the command
 */
export async function manageWebSocketServices(args) {
  const subcommand = args[0] || 'help';
  
  switch (subcommand) {
    case 'start':
      return await startWebSocketServer();
    
    case 'stop':
      return await stopWebSocketServer();
    
    case 'status':
      return await checkWebSocketStatus();
    
    case 'debug':
      return await debugWebSocketServer();
    
    case 'help':
    default:
      return `
WebSocket command usage:
  websocket start  - Start the WebSocket server
  websocket stop   - Stop the WebSocket server
  websocket status - Check WebSocket server status
  websocket debug  - Show debug information
      `;
  }
}

export default {
  manageWebSocketServices
}; 