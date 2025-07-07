/**
 * commands.js
 * Module that exports CLI command handling functionality
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import websocketModule from './websocket.js';

// Get the directory name
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '../../');

/**
 * Available commands
 */
const commands = {
  help: {
    description: 'Display help information',
    handler: showHelp
  },
  start: {
    description: 'Start the development server',
    handler: startDevServer
  },
  build: {
    description: 'Build the project',
    handler: buildProject
  },
  test: {
    description: 'Run tests',
    handler: runTests
  },
  websocket: {
    description: 'Manage WebSocket services',
    handler: async (args) => {
      const result = await websocketModule.manageWebSocketServices(args);
      console.log(result);
    }
  }
};

/**
 * Run tests
 */
function runTests() {
  console.log('Running tests...');
  // Here you would typically run test scripts
} 

/**
 * Show help information
 */
function showHelp() {
  console.log('\nAvailable commands:');
  
  for (const [cmdName, cmdDetails] of Object.entries(commands)) {
    console.log(`  ${cmdName.padEnd(12)} - ${cmdDetails.description}`);
  }
  
  console.log('\nExample: node scripts/dev.js start\n');
}

/**
 * Start the development server
 */
function startDevServer() {
  console.log('Starting development server...');
  // Implementation would go here
}

/**
 * Build the project
 */
function buildProject() {
  console.log('Building project...');
  // Implementation would go here
}

/**
 * Run the CLI with the given arguments
 * @param {string[]} argv - Command line arguments
 */
function runCLI(argv) {
  // Extract the command and arguments (skip node and script path)
  const args = argv.slice(2);
  const command = args.shift() || 'help';
  
  // Process the command
  const commandProcessor = commands[command];
  if (commandProcessor) {
    commandProcessor.handler(args);
  } else {
    console.error(`Unknown command: ${command}`);
    showHelp();
  }
}

// Export the command handling functionality
export {
  runCLI,
  showHelp
};

export default {
  processCommand: (commandName, args = []) => {
    const command = commands[commandName];
    if (command) {
      return command.handler(args);
    } else {
      console.error(`Unknown command: ${commandName}`);
      showHelp();
      return false;
    }
  },
  showHelp
};