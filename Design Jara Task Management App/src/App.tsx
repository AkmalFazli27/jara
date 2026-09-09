import { useState, useRef, useEffect } from "react";

type Priority = "high" | "medium" | "low";
type Status = "todo" | "in-progress" | "review" | "done";

interface Task {
  id: number;
  title: string;
  description: string;
  priority: Priority;
  status: Status;
  assignee: string;
  initials: string;
  avatarColor: string;
  dueDate: string;
  tags: string[];
}

const TASKS: Task[] = [
  {
    id: 1,
    title: "Redesign onboarding flow",
    description: "Simplify the 5-step flow to 3 screens and add progress indicators.",
    priority: "high",
    status: "in-progress",
    assignee: "Mia Chen",
    initials: "MC",
    avatarColor: "#7c3aed",
    dueDate: "Sep 14",
    tags: ["Design", "UX"],
  },
  {
    id: 2,
    title: "Integrate Stripe billing",
    description: "Add subscription management and invoice history to account settings.",
    priority: "high",
    status: "todo",
    assignee: "Luca Rivera",
    initials: "LR",
    avatarColor: "#0ea5e9",
    dueDate: "Sep 18",
    tags: ["Engineering", "Payments"],
  },
  {
    id: 3,
    title: "Write Q3 release notes",
    description: "Document all features shipped in Q3 for the public changelog.",
    priority: "medium",
    status: "todo",
    assignee: "Sasha Park",
    initials: "SP",
    avatarColor: "#f59e0b",
    dueDate: "Sep 20",
    tags: ["Content"],
  },
  {
    id: 4,
    title: "Fix export CSV bug",
    description: "Columns with commas in values break the export. Needs quoting.",
    priority: "high",
    status: "review",
    assignee: "Luca Rivera",
    initials: "LR",
    avatarColor: "#0ea5e9",
    dueDate: "Sep 11",
    tags: ["Bug", "Engineering"],
  },
  {
    id: 5,
    title: "Add dark mode support",
    description: "Implement system-preference detection and a manual toggle.",
    priority: "medium",
    status: "in-progress",
    assignee: "Mia Chen",
    initials: "MC",
    avatarColor: "#7c3aed",
    dueDate: "Sep 25",
    tags: ["Design", "Engineering"],
  },
  {
    id: 6,
    title: "Accessibility audit",
    description: "Run Axe on all major flows and resolve critical and serious violations.",
    priority: "medium",
    status: "done",
    assignee: "Jordan Ellis",
    initials: "JE",
    avatarColor: "#10b981",
    dueDate: "Sep 8",
    tags: ["QA", "Accessibility"],
  },
  {
    id: 7,
    title: "Performance profiling",
    description: "Profile dashboard load time and reduce bundle size below 200 kB.",
    priority: "low",
    status: "done",
    assignee: "Jordan Ellis",
    initials: "JE",
    avatarColor: "#10b981",
    dueDate: "Sep 5",
    tags: ["Engineering"],
  },
  {
    id: 8,
    title: "Set up analytics pipeline",
    description: "Route events through Segment into Mixpanel and the data warehouse.",
    priority: "low",
    status: "todo",
    assignee: "Sasha Park",
    initials: "SP",
    avatarColor: "#f59e0b",
    dueDate: "Oct 1",
    tags: ["Data", "Engineering"],
  },
];

const PRIORITY_META: Record<Priority, { label: string; color: string; dot: string }> = {
  high: { label: "High", color: "text-rose-600 bg-rose-50", dot: "bg-rose-500" },
  medium: { label: "Medium", color: "text-amber-600 bg-amber-50", dot: "bg-amber-500" },
  low: { label: "Low", color: "text-slate-500 bg-slate-100", dot: "bg-slate-400" },
};

const STATUS_META: Record<Status, { label: string; color: string }> = {
  todo: { label: "To Do", color: "text-slate-600 bg-slate-100" },
  "in-progress": { label: "In Progress", color: "text-indigo-700 bg-indigo-50" },
  review: { label: "Review", color: "text-violet-700 bg-violet-50" },
  done: { label: "Done", color: "text-emerald-700 bg-emerald-50" },
};

const KANBAN_COLUMNS: { status: Status; label: string; accent: string }[] = [
  { status: "todo", label: "To Do", accent: "bg-slate-300" },
  { status: "in-progress", label: "In Progress", accent: "bg-indigo-400" },
  { status: "review", label: "Review", accent: "bg-violet-400" },
  { status: "done", label: "Done", accent: "bg-emerald-400" },
];

type View = "list" | "kanban" | "dashboard";

const NAV_ITEMS: { id: View | "settings"; label: string; icon: React.ReactNode }[] = [
  {
    id: "dashboard",
    label: "Dashboard",
    icon: (
      <svg width="18" height="18" fill="none" stroke="currentColor" strokeWidth="1.75" viewBox="0 0 24 24">
        <rect x="3" y="3" width="7" height="7" rx="1.5" />
        <rect x="14" y="3" width="7" height="7" rx="1.5" />
        <rect x="3" y="14" width="7" height="7" rx="1.5" />
        <rect x="14" y="14" width="7" height="7" rx="1.5" />
      </svg>
    ),
  },
  {
    id: "list",
    label: "Task List",
    icon: (
      <svg width="18" height="18" fill="none" stroke="currentColor" strokeWidth="1.75" viewBox="0 0 24 24">
        <line x1="8" y1="6" x2="21" y2="6" />
        <line x1="8" y1="12" x2="21" y2="12" />
        <line x1="8" y1="18" x2="21" y2="18" />
        <circle cx="3.5" cy="6" r="1.5" fill="currentColor" stroke="none" />
        <circle cx="3.5" cy="12" r="1.5" fill="currentColor" stroke="none" />
        <circle cx="3.5" cy="18" r="1.5" fill="currentColor" stroke="none" />
      </svg>
    ),
  },
  {
    id: "kanban",
    label: "Kanban Board",
    icon: (
      <svg width="18" height="18" fill="none" stroke="currentColor" strokeWidth="1.75" viewBox="0 0 24 24">
        <rect x="3" y="3" width="5" height="18" rx="1.5" />
        <rect x="10" y="3" width="5" height="12" rx="1.5" />
        <rect x="17" y="3" width="5" height="8" rx="1.5" />
      </svg>
    ),
  },
];

function Avatar({ initials, color, size = 28 }: { initials: string; color: string; size?: number }) {
  return (
    <div
      style={{ width: size, height: size, background: color, fontSize: size * 0.36 }}
      className="rounded-full flex items-center justify-center text-white font-semibold shrink-0 select-none"
    >
      {initials}
    </div>
  );
}

function PriorityBadge({ priority }: { priority: Priority }) {
  const m = PRIORITY_META[priority];
  return (
    <span className={`inline-flex items-center gap-1.5 text-xs font-medium px-2 py-0.5 rounded-full ${m.color}`}>
      <span className={`w-1.5 h-1.5 rounded-full ${m.dot}`} />
      {m.label}
    </span>
  );
}

function StatusBadge({ status }: { status: Status }) {
  const m = STATUS_META[status];
  return (
    <span className={`text-xs font-medium px-2.5 py-0.5 rounded-full ${m.color}`}>{m.label}</span>
  );
}

function TagChip({ label }: { label: string }) {
  return (
    <span className="text-xs text-slate-500 bg-slate-50 border border-slate-200 px-2 py-0.5 rounded-md font-medium">
      {label}
    </span>
  );
}

function TaskCard({ task, onClick }: { task: Task; onClick: () => void }) {
  return (
    <div
      onClick={onClick}
      className="bg-white rounded-xl border border-slate-200 p-4 cursor-pointer hover:shadow-md hover:border-indigo-200 transition-all duration-150 group"
    >
      <div className="flex items-start justify-between gap-3 mb-2">
        <p className="text-sm font-semibold text-slate-800 leading-snug group-hover:text-indigo-700 transition-colors">
          {task.title}
        </p>
        <PriorityBadge priority={task.priority} />
      </div>
      <p className="text-xs text-slate-500 leading-relaxed mb-3 line-clamp-2">{task.description}</p>
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-1.5">
          {task.tags.slice(0, 2).map((t) => (
            <TagChip key={t} label={t} />
          ))}
        </div>
        <div className="flex items-center gap-2">
          <span className="text-xs text-slate-400">{task.dueDate}</span>
          <Avatar initials={task.initials} color={task.avatarColor} size={24} />
        </div>
      </div>
    </div>
  );
}

function DashboardView({ tasks, onNavigate }: { tasks: Task[]; onNavigate: (v: View) => void }) {
  const stats = [
    { label: "Total Tasks", value: tasks.length, sub: "across all projects", accent: "bg-indigo-50 text-indigo-700" },
    {
      label: "In Progress",
      value: tasks.filter((t) => t.status === "in-progress").length,
      sub: "active right now",
      accent: "bg-violet-50 text-violet-700",
    },
    {
      label: "Due This Week",
      value: tasks.filter((t) => t.dueDate.startsWith("Sep")).length,
      sub: "need attention",
      accent: "bg-amber-50 text-amber-700",
    },
    {
      label: "Completed",
      value: tasks.filter((t) => t.status === "done").length,
      sub: "tasks closed",
      accent: "bg-emerald-50 text-emerald-700",
    },
  ];

  const recent = tasks.filter((t) => t.status !== "done").slice(0, 4);
  const byAssignee = Object.entries(
    tasks.reduce<Record<string, { count: number; initials: string; color: string }>>(
      (acc, t) => {
        if (!acc[t.assignee]) acc[t.assignee] = { count: 0, initials: t.initials, color: t.avatarColor };
        acc[t.assignee].count++;
        return acc;
      },
      {}
    )
  );

  return (
    <div className="space-y-8">
      <div>
        <h2 className="text-xl font-bold text-slate-900 mb-1">Good morning, Alex</h2>
        <p className="text-sm text-slate-500">Here's what's on your plate for today, September 9.</p>
      </div>

      <div className="grid grid-cols-2 xl:grid-cols-4 gap-4">
        {stats.map((s) => (
          <div key={s.label} className="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">{s.label}</p>
            <p className="text-3xl font-bold text-slate-900 mb-1">{s.value}</p>
            <span className={`text-xs font-medium px-2 py-0.5 rounded-full ${s.accent}`}>{s.sub}</span>
          </div>
        ))}
      </div>

      <div className="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div className="xl:col-span-2 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
          <div className="flex items-center justify-between mb-5">
            <h3 className="text-sm font-bold text-slate-800">Recent Tasks</h3>
            <button
              onClick={() => onNavigate("list")}
              className="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors"
            >
              View all →
            </button>
          </div>
          <div className="space-y-2">
            {recent.map((task) => (
              <div
                key={task.id}
                className="flex items-center justify-between gap-4 px-3 py-2.5 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer"
              >
                <div className="flex items-center gap-3 min-w-0">
                  <Avatar initials={task.initials} color={task.avatarColor} size={26} />
                  <div className="min-w-0">
                    <p className="text-sm font-medium text-slate-800 truncate">{task.title}</p>
                    <p className="text-xs text-slate-400">Due {task.dueDate}</p>
                  </div>
                </div>
                <div className="flex items-center gap-2 shrink-0">
                  <StatusBadge status={task.status} />
                  <PriorityBadge priority={task.priority} />
                </div>
              </div>
            ))}
          </div>
        </div>

        <div className="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
          <h3 className="text-sm font-bold text-slate-800 mb-5">Team Workload</h3>
          <div className="space-y-4">
            {byAssignee.map(([name, data]) => {
              const pct = Math.round((data.count / tasks.length) * 100);
              return (
                <div key={name}>
                  <div className="flex items-center gap-3 mb-2">
                    <Avatar initials={data.initials} color={data.color} size={28} />
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center justify-between">
                        <p className="text-xs font-semibold text-slate-700 truncate">{name}</p>
                        <p className="text-xs text-slate-500 ml-2">{data.count} tasks</p>
                      </div>
                    </div>
                  </div>
                  <div className="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div
                      className="h-full rounded-full transition-all duration-500"
                      style={{ width: `${pct}%`, background: data.color }}
                    />
                  </div>
                </div>
              );
            })}
          </div>
          <div className="mt-6 pt-5 border-t border-slate-100">
            <p className="text-xs font-bold text-slate-700 mb-3">By Priority</p>
            {(["high", "medium", "low"] as Priority[]).map((p) => {
              const count = tasks.filter((t) => t.priority === p).length;
              const m = PRIORITY_META[p];
              return (
                <div key={p} className="flex items-center justify-between mb-2">
                  <span className={`text-xs font-medium px-2 py-0.5 rounded-full ${m.color}`}>{m.label}</span>
                  <span className="text-xs font-semibold text-slate-700">{count}</span>
                </div>
              );
            })}
          </div>
        </div>
      </div>
    </div>
  );
}

function ListView({ tasks }: { tasks: Task[] }) {
  const [filter, setFilter] = useState<Status | "all">("all");
  const [search, setSearch] = useState("");

  const filtered = tasks.filter((t) => {
    const matchStatus = filter === "all" || t.status === filter;
    const matchSearch =
      search === "" ||
      t.title.toLowerCase().includes(search.toLowerCase()) ||
      t.assignee.toLowerCase().includes(search.toLowerCase());
    return matchStatus && matchSearch;
  });

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-xl font-bold text-slate-900">Task List</h2>
        <button className="flex items-center gap-1.5 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors shadow-sm">
          <svg width="14" height="14" fill="none" stroke="currentColor" strokeWidth="2.5" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
          </svg>
          New Task
        </button>
      </div>

      <div className="flex flex-wrap items-center gap-3 mb-5">
        <div className="relative flex-1 min-w-[200px] max-w-xs">
          <svg
            className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
            width="14"
            height="14"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            viewBox="0 0 24 24"
          >
            <circle cx="11" cy="11" r="8" />
            <line x1="21" y1="21" x2="16.65" y2="16.65" />
          </svg>
          <input
            className="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 placeholder-slate-400"
            placeholder="Search tasks..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
        </div>
        <div className="flex items-center gap-1 bg-slate-100 rounded-lg p-1">
          {(["all", "todo", "in-progress", "review", "done"] as const).map((s) => (
            <button
              key={s}
              onClick={() => setFilter(s)}
              className={`text-xs font-semibold px-3 py-1.5 rounded-md transition-all ${
                filter === s ? "bg-white text-slate-900 shadow-sm" : "text-slate-500 hover:text-slate-700"
              }`}
            >
              {s === "all" ? "All" : STATUS_META[s].label}
            </button>
          ))}
        </div>
      </div>

      <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table className="w-full">
          <thead>
            <tr className="border-b border-slate-100">
              <th className="text-left text-xs font-semibold text-slate-500 px-5 py-3">Task</th>
              <th className="text-left text-xs font-semibold text-slate-500 px-4 py-3 hidden md:table-cell">Status</th>
              <th className="text-left text-xs font-semibold text-slate-500 px-4 py-3 hidden lg:table-cell">Priority</th>
              <th className="text-left text-xs font-semibold text-slate-500 px-4 py-3 hidden lg:table-cell">Assignee</th>
              <th className="text-left text-xs font-semibold text-slate-500 px-4 py-3 hidden xl:table-cell">Tags</th>
              <th className="text-left text-xs font-semibold text-slate-500 px-5 py-3">Due</th>
            </tr>
          </thead>
          <tbody>
            {filtered.map((task, i) => (
              <tr
                key={task.id}
                className={`hover:bg-slate-50 transition-colors cursor-pointer ${i !== filtered.length - 1 ? "border-b border-slate-100" : ""}`}
              >
                <td className="px-5 py-3.5">
                  <p className="text-sm font-semibold text-slate-800">{task.title}</p>
                  <p className="text-xs text-slate-400 mt-0.5 line-clamp-1 hidden sm:block">{task.description}</p>
                </td>
                <td className="px-4 py-3.5 hidden md:table-cell">
                  <StatusBadge status={task.status} />
                </td>
                <td className="px-4 py-3.5 hidden lg:table-cell">
                  <PriorityBadge priority={task.priority} />
                </td>
                <td className="px-4 py-3.5 hidden lg:table-cell">
                  <div className="flex items-center gap-2">
                    <Avatar initials={task.initials} color={task.avatarColor} size={24} />
                    <span className="text-xs text-slate-600 font-medium">{task.assignee}</span>
                  </div>
                </td>
                <td className="px-4 py-3.5 hidden xl:table-cell">
                  <div className="flex gap-1">
                    {task.tags.slice(0, 2).map((t) => (
                      <TagChip key={t} label={t} />
                    ))}
                  </div>
                </td>
                <td className="px-5 py-3.5">
                  <span className="text-xs font-medium text-slate-500">{task.dueDate}</span>
                </td>
              </tr>
            ))}
            {filtered.length === 0 && (
              <tr>
                <td colSpan={6} className="px-5 py-12 text-center text-sm text-slate-400">
                  No tasks match your filters.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}

function KanbanView({ tasks }: { tasks: Task[] }) {
  const [selected, setSelected] = useState<Task | null>(null);

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-xl font-bold text-slate-900">Kanban Board</h2>
        <button className="flex items-center gap-1.5 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors shadow-sm">
          <svg width="14" height="14" fill="none" stroke="currentColor" strokeWidth="2.5" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
          </svg>
          New Task
        </button>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 items-start">
        {KANBAN_COLUMNS.map((col) => {
          const colTasks = tasks.filter((t) => t.status === col.status);
          return (
            <div key={col.status} className="bg-slate-50 rounded-2xl border border-slate-200 p-3">
              <div className="flex items-center gap-2 mb-3 px-1">
                <span className={`w-2 h-2 rounded-full ${col.accent}`} />
                <span className="text-xs font-bold text-slate-700">{col.label}</span>
                <span className="ml-auto text-xs font-semibold text-slate-400 bg-white border border-slate-200 rounded-full px-2 py-0.5">
                  {colTasks.length}
                </span>
              </div>
              <div className="space-y-2">
                {colTasks.map((task) => (
                  <TaskCard key={task.id} task={task} onClick={() => setSelected(task)} />
                ))}
                {colTasks.length === 0 && (
                  <div className="text-center py-8 text-xs text-slate-400">No tasks</div>
                )}
              </div>
            </div>
          );
        })}
      </div>

      {selected && (
        <div
          className="fixed inset-0 bg-slate-900/30 backdrop-blur-sm z-50 flex items-center justify-center p-4"
          onClick={() => setSelected(null)}
        >
          <div
            className="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-md p-6"
            onClick={(e) => e.stopPropagation()}
          >
            <div className="flex items-start justify-between gap-4 mb-4">
              <h3 className="text-base font-bold text-slate-900 leading-snug">{selected.title}</h3>
              <button
                onClick={() => setSelected(null)}
                className="text-slate-400 hover:text-slate-600 transition-colors mt-0.5 shrink-0"
              >
                <svg width="18" height="18" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                  <line x1="18" y1="6" x2="6" y2="18" />
                  <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
              </button>
            </div>
            <p className="text-sm text-slate-500 leading-relaxed mb-5">{selected.description}</p>
            <div className="grid grid-cols-2 gap-3 mb-5">
              <div>
                <p className="text-xs text-slate-400 mb-1">Status</p>
                <StatusBadge status={selected.status} />
              </div>
              <div>
                <p className="text-xs text-slate-400 mb-1">Priority</p>
                <PriorityBadge priority={selected.priority} />
              </div>
              <div>
                <p className="text-xs text-slate-400 mb-1">Due date</p>
                <p className="text-sm font-medium text-slate-700">{selected.dueDate}</p>
              </div>
              <div>
                <p className="text-xs text-slate-400 mb-1">Assignee</p>
                <div className="flex items-center gap-2">
                  <Avatar initials={selected.initials} color={selected.avatarColor} size={22} />
                  <p className="text-sm font-medium text-slate-700">{selected.assignee}</p>
                </div>
              </div>
            </div>
            <div>
              <p className="text-xs text-slate-400 mb-2">Tags</p>
              <div className="flex flex-wrap gap-1.5">
                {selected.tags.map((t) => (
                  <TagChip key={t} label={t} />
                ))}
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

/* ─── Productivity illustration ─────────────────────────────────────────── */
function ProductivityIllustration() {
  return (
    <svg viewBox="0 0 480 520" fill="none" xmlns="http://www.w3.org/2000/svg" className="w-full max-w-md">
      {/* Background blobs */}
      <ellipse cx="240" cy="260" rx="210" ry="210" fill="white" fillOpacity="0.04" />
      <ellipse cx="340" cy="160" rx="120" ry="120" fill="white" fillOpacity="0.05" />

      {/* Main board — large card */}
      <rect x="60" y="80" width="360" height="280" rx="20" fill="white" fillOpacity="0.10" stroke="white" strokeOpacity="0.18" strokeWidth="1.5" />

      {/* Board header strip */}
      <rect x="60" y="80" width="360" height="44" rx="20" fill="white" fillOpacity="0.08" />
      <rect x="60" y="104" width="360" height="20" fill="white" fillOpacity="0.08" />
      {/* Dots */}
      <circle cx="92" cy="102" r="5" fill="#f87171" fillOpacity="0.7" />
      <circle cx="112" cy="102" r="5" fill="#fbbf24" fillOpacity="0.7" />
      <circle cx="132" cy="102" r="5" fill="#34d399" fillOpacity="0.7" />
      {/* Board title */}
      <rect x="160" y="96" width="80" height="12" rx="4" fill="white" fillOpacity="0.3" />

      {/* Three kanban columns */}
      {/* Col 1 — To Do */}
      <rect x="82" y="144" width="96" height="196" rx="12" fill="white" fillOpacity="0.06" stroke="white" strokeOpacity="0.1" strokeWidth="1" />
      <rect x="94" y="156" width="52" height="8" rx="3" fill="white" fillOpacity="0.35" />
      {/* Cards in col 1 */}
      <rect x="90" y="174" width="80" height="44" rx="8" fill="white" fillOpacity="0.12" stroke="white" strokeOpacity="0.15" strokeWidth="1" />
      <rect x="98" y="183" width="50" height="7" rx="3" fill="white" fillOpacity="0.5" />
      <rect x="98" y="195" width="36" height="5" rx="2.5" fill="white" fillOpacity="0.25" />
      <rect x="98" y="204" width="20" height="5" rx="2.5" fill="#fbbf24" fillOpacity="0.7" />
      <rect x="90" y="226" width="80" height="44" rx="8" fill="white" fillOpacity="0.08" stroke="white" strokeOpacity="0.10" strokeWidth="1" />
      <rect x="98" y="235" width="42" height="7" rx="3" fill="white" fillOpacity="0.4" />
      <rect x="98" y="247" width="30" height="5" rx="2.5" fill="white" fillOpacity="0.2" />
      <rect x="98" y="256" width="22" height="5" rx="2.5" fill="#f87171" fillOpacity="0.65" />
      <rect x="90" y="278" width="80" height="44" rx="8" fill="white" fillOpacity="0.06" stroke="white" strokeOpacity="0.08" strokeWidth="1" />
      <rect x="98" y="287" width="38" height="7" rx="3" fill="white" fillOpacity="0.3" />
      <rect x="98" y="299" width="28" height="5" rx="2.5" fill="white" fillOpacity="0.18" />

      {/* Col 2 — In Progress */}
      <rect x="192" y="144" width="96" height="196" rx="12" fill="white" fillOpacity="0.06" stroke="white" strokeOpacity="0.1" strokeWidth="1" />
      <rect x="204" y="156" width="64" height="8" rx="3" fill="#a5b4fc" fillOpacity="0.6" />
      {/* Cards col 2 */}
      <rect x="200" y="174" width="80" height="52" rx="8" fill="white" fillOpacity="0.14" stroke="white" strokeOpacity="0.18" strokeWidth="1" />
      <rect x="208" y="184" width="54" height="7" rx="3" fill="white" fillOpacity="0.6" />
      <rect x="208" y="196" width="40" height="5" rx="2.5" fill="white" fillOpacity="0.3" />
      <rect x="208" y="207" width="24" height="5" rx="2.5" fill="#a5b4fc" fillOpacity="0.8" />
      {/* Progress bar */}
      <rect x="208" y="217" width="52" height="3" rx="1.5" fill="white" fillOpacity="0.12" />
      <rect x="208" y="217" width="34" height="3" rx="1.5" fill="#818cf8" fillOpacity="0.9" />
      <rect x="200" y="234" width="80" height="44" rx="8" fill="white" fillOpacity="0.09" stroke="white" strokeOpacity="0.12" strokeWidth="1" />
      <rect x="208" y="244" width="44" height="7" rx="3" fill="white" fillOpacity="0.45" />
      <rect x="208" y="256" width="32" height="5" rx="2.5" fill="white" fillOpacity="0.22" />
      <rect x="208" y="265" width="52" height="3" rx="1.5" fill="white" fillOpacity="0.12" />
      <rect x="208" y="265" width="20" height="3" rx="1.5" fill="#818cf8" fillOpacity="0.7" />

      {/* Col 3 — Done */}
      <rect x="302" y="144" width="96" height="196" rx="12" fill="white" fillOpacity="0.06" stroke="white" strokeOpacity="0.1" strokeWidth="1" />
      <rect x="314" y="156" width="42" height="8" rx="3" fill="#6ee7b7" fillOpacity="0.5" />
      {/* Cards col 3 */}
      <rect x="310" y="174" width="80" height="44" rx="8" fill="white" fillOpacity="0.07" stroke="white" strokeOpacity="0.08" strokeWidth="1" />
      <rect x="318" y="184" width="46" height="7" rx="3" fill="white" fillOpacity="0.35" />
      <rect x="318" y="196" width="30" height="5" rx="2.5" fill="white" fillOpacity="0.18" />
      {/* Check mark */}
      <circle cx="358" cy="208" r="7" fill="#34d399" fillOpacity="0.25" />
      <path d="M354 208l3 3 5-5" stroke="#34d399" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
      <rect x="310" y="226" width="80" height="44" rx="8" fill="white" fillOpacity="0.05" stroke="white" strokeOpacity="0.06" strokeWidth="1" />
      <rect x="318" y="236" width="38" height="7" rx="3" fill="white" fillOpacity="0.25" />
      <rect x="318" y="248" width="24" height="5" rx="2.5" fill="white" fillOpacity="0.14" />
      <circle cx="358" cy="260" r="7" fill="#34d399" fillOpacity="0.2" />
      <path d="M354 260l3 3 5-5" stroke="#34d399" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />

      {/* ── Avatar cluster bottom-left of board ── */}
      <circle cx="92" cy="336" r="12" fill="#7c3aed" />
      <text x="92" y="340" textAnchor="middle" fill="white" fontSize="9" fontWeight="600" fontFamily="Inter, sans-serif">MC</text>
      <circle cx="110" cy="336" r="12" fill="#0ea5e9" />
      <text x="110" y="340" textAnchor="middle" fill="white" fontSize="9" fontWeight="600" fontFamily="Inter, sans-serif">LR</text>
      <circle cx="128" cy="336" r="12" fill="#10b981" />
      <text x="128" y="340" textAnchor="middle" fill="white" fontSize="9" fontWeight="600" fontFamily="Inter, sans-serif">JE</text>
      <rect x="148" y="328" width="44" height="16" rx="8" fill="white" fillOpacity="0.12" />
      <text x="170" y="340" textAnchor="middle" fill="white" fontSize="8" fontWeight="500" fontFamily="Inter, sans-serif" fillOpacity="0.7">+4 more</text>

      {/* ── Floating stat pill top-right ── */}
      <rect x="290" y="48" width="130" height="36" rx="18" fill="white" fillOpacity="0.12" stroke="white" strokeOpacity="0.2" strokeWidth="1" />
      <circle cx="314" cy="66" r="8" fill="#818cf8" fillOpacity="0.4" />
      <path d="M311 66l2 2 4-4" stroke="#c7d2fe" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
      <rect x="328" y="59" width="50" height="7" rx="3" fill="white" fillOpacity="0.55" />
      <rect x="328" y="70" width="34" height="5" rx="2.5" fill="white" fillOpacity="0.3" />

      {/* ── Floating notification pill bottom-right ── */}
      <rect x="290" y="380" width="150" height="56" rx="16" fill="white" fillOpacity="0.10" stroke="white" strokeOpacity="0.18" strokeWidth="1" />
      <circle cx="314" cy="404" r="10" fill="#4f46e5" fillOpacity="0.5" />
      <text x="314" y="408" textAnchor="middle" fill="white" fontSize="9" fontWeight="700" fontFamily="Inter, sans-serif">SP</text>
      <rect x="330" y="396" width="56" height="7" rx="3" fill="white" fillOpacity="0.5" />
      <rect x="330" y="407" width="72" height="5" rx="2.5" fill="white" fillOpacity="0.25" />
      <rect x="330" y="416" width="44" height="5" rx="2.5" fill="#a5b4fc" fillOpacity="0.6" />

      {/* ── Small floating checkmark card bottom-left ── */}
      <rect x="40" y="388" width="120" height="52" rx="14" fill="white" fillOpacity="0.10" stroke="white" strokeOpacity="0.15" strokeWidth="1" />
      <rect x="56" y="400" width="56" height="7" rx="3" fill="white" fillOpacity="0.5" />
      <rect x="56" y="412" width="36" height="5" rx="2.5" fill="white" fillOpacity="0.25" />
      <circle cx="134" cy="414" r="9" fill="#34d399" fillOpacity="0.25" />
      <path d="M130 414l3 3 5-5" stroke="#34d399" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />

      {/* ── Dashed connector lines ── */}
      <line x1="160" y1="414" x2="290" y2="414" stroke="white" strokeOpacity="0.08" strokeWidth="1" strokeDasharray="4 4" />
      <line x1="240" y1="370" x2="240" y2="388" stroke="white" strokeOpacity="0.08" strokeWidth="1" strokeDasharray="4 4" />
    </svg>
  );
}

/* ─── Login screen ──────────────────────────────────────────────────────── */
function LoginScreen({ onLogin }: { onLogin: () => void }) {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPw, setShowPw] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const emailRef = useRef<HTMLInputElement>(null);

  useEffect(() => { emailRef.current?.focus(); }, []);

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (!email || !password) { setError("Please enter your email and password."); return; }
    setError("");
    setLoading(true);
    setTimeout(() => { setLoading(false); onLogin(); }, 900);
  }

  return (
    <div className="min-h-screen flex font-sans">
      {/* ── Left: form panel ── */}
      <div className="flex-1 flex flex-col justify-center px-8 sm:px-16 lg:px-20 xl:px-28 bg-white min-w-0">
        <div className="w-full max-w-sm mx-auto">
          {/* Logo */}
          <div className="flex items-center gap-2.5 mb-12">
            <div className="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center shadow-md shadow-indigo-200">
              <svg width="16" height="16" fill="none" stroke="white" strokeWidth="2.2" viewBox="0 0 24 24">
                <path d="M9 11l3 3L22 4" />
                <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" />
              </svg>
            </div>
            <span className="text-lg font-bold text-slate-900 tracking-tight">Jara</span>
          </div>

          {/* Heading */}
          <h1 className="text-[2rem] font-bold text-slate-900 leading-tight mb-2">
            Welcome back
          </h1>
          <p className="text-sm text-slate-500 mb-9">
            Sign in to your workspace to continue.
          </p>

          {/* Form */}
          <form onSubmit={handleSubmit} className="space-y-5" noValidate>
            {/* Email */}
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1.5" htmlFor="email">
                Email address
              </label>
              <input
                id="email"
                ref={emailRef}
                type="email"
                autoComplete="email"
                placeholder="alex@jara.app"
                value={email}
                onChange={(e) => { setEmail(e.target.value); setError(""); }}
                className="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl bg-white text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-shadow"
              />
            </div>

            {/* Password */}
            <div>
              <div className="flex items-center justify-between mb-1.5">
                <label className="block text-xs font-semibold text-slate-700" htmlFor="password">
                  Password
                </label>
                <button
                  type="button"
                  className="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors"
                >
                  Forgot password?
                </button>
              </div>
              <div className="relative">
                <input
                  id="password"
                  type={showPw ? "text" : "password"}
                  autoComplete="current-password"
                  placeholder="••••••••"
                  value={password}
                  onChange={(e) => { setPassword(e.target.value); setError(""); }}
                  className="w-full px-4 py-3 pr-11 text-sm border border-slate-200 rounded-xl bg-white text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-shadow"
                />
                <button
                  type="button"
                  onClick={() => setShowPw((v) => !v)}
                  className="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
                  aria-label={showPw ? "Hide password" : "Show password"}
                >
                  {showPw ? (
                    <svg width="16" height="16" fill="none" stroke="currentColor" strokeWidth="1.75" viewBox="0 0 24 24">
                      <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24" />
                      <line x1="1" y1="1" x2="23" y2="23" />
                    </svg>
                  ) : (
                    <svg width="16" height="16" fill="none" stroke="currentColor" strokeWidth="1.75" viewBox="0 0 24 24">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                      <circle cx="12" cy="12" r="3" />
                    </svg>
                  )}
                </button>
              </div>
            </div>

            {/* Error */}
            {error && (
              <p className="text-xs text-rose-600 font-medium -mt-1">{error}</p>
            )}

            {/* Submit */}
            <button
              type="submit"
              disabled={loading}
              className="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold transition-all shadow-md shadow-indigo-200 hover:shadow-lg hover:shadow-indigo-200 disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2 mt-1"
            >
              {loading ? (
                <>
                  <svg className="animate-spin" width="15" height="15" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="white" strokeWidth="3" />
                    <path className="opacity-75" fill="white" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                  </svg>
                  Signing in…
                </>
              ) : (
                "Log In"
              )}
            </button>
          </form>

          {/* Divider */}
          <div className="flex items-center gap-3 my-7">
            <div className="flex-1 h-px bg-slate-100" />
            <span className="text-xs text-slate-400 font-medium">or</span>
            <div className="flex-1 h-px bg-slate-100" />
          </div>

          {/* SSO button */}
          <button className="w-full flex items-center justify-center gap-3 py-3 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
            <svg width="16" height="16" viewBox="0 0 24 24">
              <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
              <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
              <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
              <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Continue with Google
          </button>

          <p className="mt-8 text-center text-xs text-slate-400">
            Don&apos;t have an account?{" "}
            <button className="font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">
              Sign up free
            </button>
          </p>
        </div>
      </div>

      {/* ── Right: illustration panel ── */}
      <div className="hidden lg:flex flex-1 relative items-center justify-center overflow-hidden bg-indigo-700"
        style={{ background: "linear-gradient(135deg, #3730a3 0%, #4f46e5 45%, #7c3aed 100%)" }}
      >
        {/* Subtle grid overlay */}
        <div
          className="absolute inset-0 opacity-[0.04]"
          style={{
            backgroundImage:
              "linear-gradient(white 1px, transparent 1px), linear-gradient(90deg, white 1px, transparent 1px)",
            backgroundSize: "40px 40px",
          }}
        />
        {/* Glow orbs */}
        <div className="absolute top-1/4 left-1/3 w-64 h-64 rounded-full bg-violet-500 opacity-20 blur-3xl pointer-events-none" />
        <div className="absolute bottom-1/4 right-1/4 w-48 h-48 rounded-full bg-indigo-300 opacity-15 blur-3xl pointer-events-none" />

        <div className="relative z-10 flex flex-col items-center px-12 text-center">
          <ProductivityIllustration />
          <h2 className="mt-6 text-2xl font-bold text-white leading-snug">
            Every task, one place.
          </h2>
          <p className="mt-3 text-sm text-indigo-200 leading-relaxed max-w-xs">
            Assign, track, and ship work together — no status meetings required.
          </p>

          {/* Social proof */}
          <div className="mt-8 flex items-center gap-3">
            <div className="flex -space-x-2">
              {(["#7c3aed","#0ea5e9","#10b981","#f59e0b"] as const).map((c, i) => (
                <div key={i} style={{ background: c }} className="w-7 h-7 rounded-full border-2 border-indigo-700 flex items-center justify-center text-white text-[9px] font-bold">
                  {["MC","LR","JE","SP"][i]}
                </div>
              ))}
            </div>
            <p className="text-xs text-indigo-200 font-medium">
              Trusted by <span className="text-white font-bold">12,000+</span> teams
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}

/* ─── Extended data types ───────────────────────────────────────────────── */
interface Subtask {
  id: number;
  title: string;
  done: boolean;
}

interface ActivityEntry {
  id: number;
  author: string;
  initials: string;
  avatarColor: string;
  time: string;
  content: string;
  isComment: boolean;
}

interface ProjectTask {
  id: number;
  title: string;
  priority: Priority;
  status: Status;
  assignee: string;
  initials: string;
  avatarColor: string;
  dueDate: string;
  overdue: boolean;
  subtasks: Subtask[];
  description: string;
  activity: ActivityEntry[];
}

interface Project {
  id: number;
  name: string;
  color: string;
  tasks: ProjectTask[];
}

const DEMO_PROJECTS: Project[] = [
  {
    id: 1,
    name: "Rebrand 2026",
    color: "#7c3aed",
    tasks: [
      {
        id: 101, title: "Audit existing brand assets", priority: "high", status: "done",
        assignee: "Mia Chen", initials: "MC", avatarColor: "#7c3aed",
        dueDate: "Sep 2", overdue: false,
        description: "Catalog all logo files, color swatches, typography specimens, and brand guidelines in use across marketing, product, and support.",
        subtasks: [
          { id: 1, title: "Export all logo variants from Figma", done: true },
          { id: 2, title: "Document typeface usage per channel", done: true },
          { id: 3, title: "Compile competitor brand audit", done: false },
        ],
        activity: [
          { id: 1, author: "Mia Chen", initials: "MC", avatarColor: "#7c3aed", time: "Sep 2, 10:14 AM", content: "Moved to Done — all assets exported and catalogued.", isComment: false },
          { id: 2, author: "Alex Kim", initials: "AK", avatarColor: "#4f46e5", time: "Sep 1, 3:45 PM", content: "Can you also grab the Figma frames for the onboarding illustrations?", isComment: true },
          { id: 3, author: "Mia Chen", initials: "MC", avatarColor: "#7c3aed", time: "Sep 1, 4:02 PM", content: "Done, added them to the shared folder.", isComment: true },
        ],
      },
      {
        id: 102, title: "Define new color palette", priority: "high", status: "in-progress",
        assignee: "Mia Chen", initials: "MC", avatarColor: "#7c3aed",
        dueDate: "Sep 9", overdue: true,
        description: "Establish primary, secondary, and neutral color scales with accessible contrast ratios. Deliver as a Figma token file and CSS custom properties.",
        subtasks: [
          { id: 1, title: "Explore palette directions (min 3)", done: true },
          { id: 2, title: "Run WCAG contrast checks on all pairs", done: false },
          { id: 3, title: "Export as Figma tokens", done: false },
        ],
        activity: [
          { id: 1, author: "Mia Chen", initials: "MC", avatarColor: "#7c3aed", time: "Sep 7, 9:30 AM", content: "Started exploring three directions — will share Figma link EOD.", isComment: true },
          { id: 2, author: "Luca Rivera", initials: "LR", avatarColor: "#0ea5e9", time: "Sep 7, 11:00 AM", content: "Priority for the engineering handoff is the CSS variable file.", isComment: true },
        ],
      },
      {
        id: 103, title: "Redesign logo mark", priority: "high", status: "in-progress",
        assignee: "Mia Chen", initials: "MC", avatarColor: "#7c3aed",
        dueDate: "Sep 12", overdue: false,
        description: "Create three logo mark concepts for stakeholder review. One wordmark, one icon mark, one combination. All must work at 16px and 512px.",
        subtasks: [
          { id: 1, title: "Sketch 10 rough concepts", done: true },
          { id: 2, title: "Refine top 3 to Figma vectors", done: false },
          { id: 3, title: "Stakeholder review session", done: false },
        ],
        activity: [
          { id: 1, author: "Mia Chen", initials: "MC", avatarColor: "#7c3aed", time: "Sep 6, 2:00 PM", content: "Concepts are underway. Leaning toward a geometric mark.", isComment: true },
        ],
      },
      {
        id: 104, title: "Update marketing site copy", priority: "medium", status: "todo",
        assignee: "Sasha Park", initials: "SP", avatarColor: "#f59e0b",
        dueDate: "Sep 18", overdue: false,
        description: "Rewrite homepage hero, about page, and pricing page copy to align with the new brand voice guide.",
        subtasks: [
          { id: 1, title: "Receive brand voice guide", done: false },
          { id: 2, title: "Draft homepage hero copy", done: false },
        ],
        activity: [],
      },
      {
        id: 105, title: "Typography system spec", priority: "medium", status: "todo",
        assignee: "Jordan Ellis", initials: "JE", avatarColor: "#10b981",
        dueDate: "Sep 5", overdue: true,
        description: "Specify font families, weights, sizes, line-heights, and letter-spacing for all heading and body levels. Deliver as a Figma component library.",
        subtasks: [],
        activity: [
          { id: 1, author: "Jordan Ellis", initials: "JE", avatarColor: "#10b981", time: "Sep 4, 5:00 PM", content: "Blocked — waiting on final font licensing confirmation.", isComment: true },
        ],
      },
      {
        id: 106, title: "Brand guidelines document", priority: "low", status: "todo",
        assignee: "Sasha Park", initials: "SP", avatarColor: "#f59e0b",
        dueDate: "Sep 30", overdue: false,
        description: "Compile logo usage rules, color palette reference, typography scale, voice & tone guide, and photography style into a single PDF and Notion page.",
        subtasks: [],
        activity: [],
      },
    ],
  },
  {
    id: 2,
    name: "Mobile App",
    color: "#0ea5e9",
    tasks: [
      {
        id: 201, title: "Implement push notifications", priority: "high", status: "in-progress",
        assignee: "Luca Rivera", initials: "LR", avatarColor: "#0ea5e9",
        dueDate: "Sep 10", overdue: true,
        description: "Integrate FCM for Android and APNs for iOS. Support task reminders, mentions, and due-date alerts.",
        subtasks: [
          { id: 1, title: "Set up Firebase project", done: true },
          { id: 2, title: "Register APNs certificate", done: true },
          { id: 3, title: "Implement notification payload schema", done: false },
          { id: 4, title: "QA on physical devices", done: false },
        ],
        activity: [
          { id: 1, author: "Luca Rivera", initials: "LR", avatarColor: "#0ea5e9", time: "Sep 8, 10:00 AM", content: "APNs cert registered. Working on payload schema now.", isComment: true },
        ],
      },
      {
        id: 202, title: "Offline mode data sync", priority: "high", status: "todo",
        assignee: "Jordan Ellis", initials: "JE", avatarColor: "#10b981",
        dueDate: "Sep 22", overdue: false,
        description: "Cache task list and project data locally using SQLite. Sync on reconnect with conflict resolution.",
        subtasks: [],
        activity: [],
      },
    ],
  },
  {
    id: 3,
    name: "API v2",
    color: "#10b981",
    tasks: [
      {
        id: 301, title: "Design REST resource schema", priority: "high", status: "done",
        assignee: "Luca Rivera", initials: "LR", avatarColor: "#0ea5e9",
        dueDate: "Aug 30", overdue: false,
        description: "Define all v2 endpoints, request/response shapes, error codes, and pagination strategy. Produce an OpenAPI 3.1 spec.",
        subtasks: [
          { id: 1, title: "Draft OpenAPI spec YAML", done: true },
          { id: 2, title: "Internal review with backend team", done: true },
        ],
        activity: [
          { id: 1, author: "Luca Rivera", initials: "LR", avatarColor: "#0ea5e9", time: "Aug 30, 3:00 PM", content: "Spec merged and published to the developer portal.", isComment: false },
        ],
      },
      {
        id: 302, title: "Rate limiting & throttling", priority: "medium", status: "in-progress",
        assignee: "Jordan Ellis", initials: "JE", avatarColor: "#10b981",
        dueDate: "Sep 15", overdue: false,
        description: "Implement sliding-window rate limiting per API key. 1000 req/min for Pro, 100 req/min for Free tier.",
        subtasks: [
          { id: 1, title: "Implement Redis sliding window", done: true },
          { id: 2, title: "Add X-RateLimit headers to all responses", done: false },
          { id: 3, title: "Load test at 10x expected peak", done: false },
        ],
        activity: [],
      },
    ],
  },
];

/* ─── Create Project Modal ──────────────────────────────────────────────── */
function CreateProjectModal({ onClose, onCreate }: { onClose: () => void; onCreate: (name: string) => void }) {
  const [name, setName] = useState("");
  const [desc, setDesc] = useState("");
  const [emailInput, setEmailInput] = useState("");
  const [invites, setInvites] = useState<string[]>([]);
  const nameRef = useRef<HTMLInputElement>(null);

  useEffect(() => { nameRef.current?.focus(); }, []);

  function addInvite() {
    const e = emailInput.trim().toLowerCase();
    if (e && e.includes("@") && !invites.includes(e)) {
      setInvites((prev) => [...prev, e]);
      setEmailInput("");
    }
  }

  function handleKeyDown(ev: React.KeyboardEvent) {
    if (ev.key === "Enter" || ev.key === ",") { ev.preventDefault(); addInvite(); }
    if (ev.key === "Escape") onClose();
  }

  function handleCreate() {
    if (!name.trim()) { nameRef.current?.focus(); return; }
    onCreate(name.trim());
    onClose();
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" onClick={onClose}>
      <div
        className="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden"
        onClick={(e) => e.stopPropagation()}
      >
        {/* Header */}
        <div className="flex items-center justify-between px-6 pt-6 pb-0">
          <div className="flex items-center gap-3">
            <div className="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
              <svg width="18" height="18" fill="none" stroke="#4f46e5" strokeWidth="2" viewBox="0 0 24 24">
                <rect x="3" y="3" width="7" height="7" rx="1.5" />
                <rect x="14" y="3" width="7" height="7" rx="1.5" />
                <rect x="3" y="14" width="7" height="7" rx="1.5" />
                <line x1="14" y1="17.5" x2="21" y2="17.5" />
                <line x1="17.5" y1="14" x2="17.5" y2="21" />
              </svg>
            </div>
            <div>
              <h2 className="text-base font-bold text-slate-900">New Project</h2>
              <p className="text-xs text-slate-400">Set up a workspace for your team</p>
            </div>
          </div>
          <button onClick={onClose} className="text-slate-400 hover:text-slate-600 transition-colors p-1">
            <svg width="18" height="18" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
              <line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </button>
        </div>

        <div className="px-6 py-5 space-y-5">
          {/* Project name */}
          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1.5">Project Name <span className="text-rose-500">*</span></label>
            <input
              ref={nameRef}
              value={name}
              onChange={(e) => setName(e.target.value)}
              onKeyDown={(e) => e.key === "Escape" && onClose()}
              placeholder="e.g. Website Redesign"
              className="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-white text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-shadow"
            />
          </div>

          {/* Description */}
          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1.5">Description <span className="text-slate-400 font-normal">(optional)</span></label>
            <textarea
              value={desc}
              onChange={(e) => setDesc(e.target.value)}
              placeholder="What is this project about? Who's the intended audience?"
              rows={3}
              className="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-white text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-shadow resize-none"
            />
          </div>

          {/* Invite team members */}
          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1.5">Invite Team Members</label>
            <div className="flex gap-2">
              <div className="flex-1 relative">
                <svg className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" width="14" height="14" fill="none" stroke="currentColor" strokeWidth="1.75" viewBox="0 0 24 24">
                  <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" /><circle cx="12" cy="7" r="4" />
                </svg>
                <input
                  value={emailInput}
                  onChange={(e) => setEmailInput(e.target.value)}
                  onKeyDown={handleKeyDown}
                  placeholder="name@company.com"
                  type="email"
                  className="w-full pl-9 pr-3 py-2.5 text-sm border border-slate-200 rounded-xl bg-white text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-shadow"
                />
              </div>
              <button
                onClick={addInvite}
                className="px-4 py-2.5 text-sm font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-xl transition-colors"
              >
                Add
              </button>
            </div>
            <p className="text-[11px] text-slate-400 mt-1.5">Press Enter or comma to add multiple emails</p>

            {invites.length > 0 && (
              <div className="flex flex-wrap gap-2 mt-3">
                {invites.map((email) => (
                  <span key={email} className="inline-flex items-center gap-1.5 text-xs bg-indigo-50 text-indigo-700 border border-indigo-100 px-2.5 py-1 rounded-full font-medium">
                    {email}
                    <button onClick={() => setInvites((p) => p.filter((e) => e !== email))} className="text-indigo-400 hover:text-indigo-700 transition-colors">
                      <svg width="10" height="10" fill="none" stroke="currentColor" strokeWidth="2.5" viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" />
                      </svg>
                    </button>
                  </span>
                ))}
              </div>
            )}
          </div>
        </div>

        {/* Footer */}
        <div className="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50/60">
          <button onClick={onClose} className="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition-colors">
            Cancel
          </button>
          <button
            onClick={handleCreate}
            disabled={!name.trim()}
            className="px-5 py-2 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-colors shadow-sm shadow-indigo-200 disabled:opacity-40 disabled:cursor-not-allowed"
          >
            Create Project
          </button>
        </div>
      </div>
    </div>
  );
}

/* ─── Task Detail Panel ─────────────────────────────────────────────────── */
function TaskDetailPanel({
  task,
  onClose,
  onComplete,
  onDelete,
}: {
  task: ProjectTask;
  onClose: () => void;
  onComplete: (id: number) => void;
  onDelete: (id: number) => void;
}) {
  const [title, setTitle] = useState(task.title);
  const [desc, setDesc] = useState(task.description);
  const [priority, setPriority] = useState<Priority>(task.priority);
  const [deadline, setDeadline] = useState("2026-09-" + task.dueDate.replace("Sep ", "").replace("Aug ", ""));
  const [subtasks, setSubtasks] = useState<Subtask[]>(task.subtasks);
  const [newSubtask, setNewSubtask] = useState("");
  const [comment, setComment] = useState("");
  const [activity, setActivity] = useState<ActivityEntry[]>(task.activity);
  const [boldActive, setBoldActive] = useState(false);
  const [italicActive, setItalicActive] = useState(false);
  const done = task.status === "done";

  function toggleSubtask(id: number) {
    setSubtasks((prev) => prev.map((s) => s.id === id ? { ...s, done: !s.done } : s));
  }

  function addSubtask() {
    if (!newSubtask.trim()) return;
    setSubtasks((prev) => [...prev, { id: Date.now(), title: newSubtask.trim(), done: false }]);
    setNewSubtask("");
  }

  function postComment() {
    if (!comment.trim()) return;
    setActivity((prev) => [
      ...prev,
      {
        id: Date.now(),
        author: "Alex Kim",
        initials: "AK",
        avatarColor: "#4f46e5",
        time: "Just now",
        content: comment.trim(),
        isComment: true,
      },
    ]);
    setComment("");
  }

  const doneCount = subtasks.filter((s) => s.done).length;

  return (
    <>
      {/* Backdrop */}
      <div className="fixed inset-0 z-40 bg-slate-900/20" onClick={onClose} />

      {/* Panel */}
      <div className="fixed inset-y-0 right-0 z-50 w-full max-w-[480px] bg-white shadow-2xl flex flex-col border-l border-slate-200 overflow-hidden">
        {/* Top action bar */}
        <div className="flex items-center gap-2 px-5 py-3.5 border-b border-slate-100 bg-slate-50/80">
          <button
            onClick={() => { onComplete(task.id); onClose(); }}
            className={`flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-sm ${
              done
                ? "bg-emerald-100 text-emerald-700 cursor-default"
                : "bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-200"
            }`}
          >
            <svg width="15" height="15" fill="none" stroke="currentColor" strokeWidth="2.2" viewBox="0 0 24 24">
              <polyline points="20 6 9 17 4 12" />
            </svg>
            {done ? "Completed" : "Mark as Complete"}
          </button>
          <button
            onClick={() => { onDelete(task.id); onClose(); }}
            className="p-2.5 rounded-xl text-rose-500 hover:bg-rose-50 hover:text-rose-700 transition-colors border border-transparent hover:border-rose-100"
            title="Delete task"
          >
            <svg width="16" height="16" fill="none" stroke="currentColor" strokeWidth="1.75" viewBox="0 0 24 24">
              <polyline points="3 6 5 6 21 6" />
              <path d="M19 6l-1 14H6L5 6" />
              <path d="M10 11v6M14 11v6" />
              <path d="M9 6V4h6v2" />
            </svg>
          </button>
          <button onClick={onClose} className="p-2.5 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
            <svg width="16" height="16" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
              <line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </button>
        </div>

        {/* Scrollable body */}
        <div className="flex-1 overflow-y-auto px-5 py-5 space-y-6">
          {/* Title */}
          <div>
            <input
              className="w-full text-xl font-bold text-slate-900 bg-transparent border-none outline-none focus:ring-0 placeholder-slate-300 leading-snug"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              placeholder="Task title"
            />
          </div>

          {/* Meta row */}
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Priority</label>
              <select
                value={priority}
                onChange={(e) => setPriority(e.target.value as Priority)}
                className="w-full text-sm border border-slate-200 rounded-lg px-2.5 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent"
              >
                <option value="high">🔴 High</option>
                <option value="medium">🟡 Medium</option>
                <option value="low">🟢 Low</option>
              </select>
            </div>
            <div>
              <label className="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Deadline</label>
              <input
                type="date"
                value={deadline}
                onChange={(e) => setDeadline(e.target.value)}
                className="w-full text-sm border border-slate-200 rounded-lg px-2.5 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent"
              />
            </div>
          </div>

          {/* Assignee */}
          <div>
            <label className="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Assignee</label>
            <div className="flex items-center gap-2.5">
              <Avatar initials={task.initials} color={task.avatarColor} size={28} />
              <span className="text-sm font-medium text-slate-700">{task.assignee}</span>
              <button className="ml-auto text-xs font-semibold text-indigo-600 hover:text-indigo-800">Change</button>
            </div>
          </div>

          <div className="h-px bg-slate-100" />

          {/* Description — minimal rich text */}
          <div>
            <label className="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Description</label>
            {/* Toolbar */}
            <div className="flex items-center gap-0.5 mb-1.5 border border-slate-200 rounded-t-lg px-2 py-1.5 bg-slate-50">
              {[
                { label: "B", style: "font-bold", active: boldActive, toggle: () => setBoldActive(v => !v) },
                { label: "I", style: "italic", active: italicActive, toggle: () => setItalicActive(v => !v) },
              ].map(({ label, style, active, toggle }) => (
                <button
                  key={label}
                  onClick={toggle}
                  className={`w-7 h-7 rounded flex items-center justify-center text-sm ${style} transition-colors ${
                    active ? "bg-indigo-100 text-indigo-700" : "text-slate-500 hover:bg-slate-100"
                  }`}
                >
                  {label}
                </button>
              ))}
              <div className="w-px h-4 bg-slate-200 mx-1" />
              {["H1", "H2"].map((h) => (
                <button key={h} className="px-1.5 h-7 rounded text-xs font-bold text-slate-500 hover:bg-slate-100 transition-colors">{h}</button>
              ))}
              <div className="w-px h-4 bg-slate-200 mx-1" />
              <button className="w-7 h-7 rounded flex items-center justify-center text-slate-500 hover:bg-slate-100 transition-colors">
                <svg width="12" height="12" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                  <line x1="8" y1="6" x2="21" y2="6" /><line x1="8" y1="12" x2="21" y2="12" /><line x1="8" y1="18" x2="21" y2="18" />
                  <line x1="3" y1="6" x2="3.01" y2="6" /><line x1="3" y1="12" x2="3.01" y2="12" /><line x1="3" y1="18" x2="3.01" y2="18" />
                </svg>
              </button>
              <button className="w-7 h-7 rounded flex items-center justify-center text-slate-500 hover:bg-slate-100 transition-colors">
                <svg width="12" height="12" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                  <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71" />
                  <path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71" />
                </svg>
              </button>
            </div>
            <textarea
              value={desc}
              onChange={(e) => setDesc(e.target.value)}
              rows={4}
              placeholder="Add a more detailed description…"
              className={`w-full px-3 py-2.5 text-sm border border-slate-200 border-t-0 rounded-b-lg bg-white text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent resize-none transition-shadow ${boldActive ? "font-bold" : ""} ${italicActive ? "italic" : ""}`}
            />
          </div>

          <div className="h-px bg-slate-100" />

          {/* Subtasks */}
          <div>
            <div className="flex items-center justify-between mb-3">
              <label className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                Subtasks
              </label>
              {subtasks.length > 0 && (
                <span className="text-xs font-semibold text-slate-500">
                  {doneCount}/{subtasks.length} done
                </span>
              )}
            </div>

            {subtasks.length > 0 && (
              <div className="mb-2 h-1 bg-slate-100 rounded-full overflow-hidden">
                <div
                  className="h-full bg-emerald-400 rounded-full transition-all duration-300"
                  style={{ width: `${(doneCount / subtasks.length) * 100}%` }}
                />
              </div>
            )}

            <div className="space-y-1.5 mb-3">
              {subtasks.map((s) => (
                <label key={s.id} className="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-50 cursor-pointer group transition-colors">
                  <div
                    onClick={() => toggleSubtask(s.id)}
                    className={`w-4.5 h-4.5 w-[18px] h-[18px] rounded-full border-2 flex items-center justify-center transition-all flex-shrink-0 ${
                      s.done ? "bg-emerald-500 border-emerald-500" : "border-slate-300 group-hover:border-indigo-400"
                    }`}
                  >
                    {s.done && (
                      <svg width="9" height="9" fill="none" stroke="white" strokeWidth="2.5" viewBox="0 0 24 24">
                        <polyline points="20 6 9 17 4 12" />
                      </svg>
                    )}
                  </div>
                  <span className={`text-sm flex-1 ${s.done ? "line-through text-slate-400" : "text-slate-700"}`}>{s.title}</span>
                </label>
              ))}
            </div>

            <div className="flex gap-2">
              <input
                value={newSubtask}
                onChange={(e) => setNewSubtask(e.target.value)}
                onKeyDown={(e) => e.key === "Enter" && addSubtask()}
                placeholder="Add a subtask…"
                className="flex-1 px-3 py-2 text-sm border border-slate-200 rounded-lg bg-white text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent"
              />
              <button
                onClick={addSubtask}
                className="px-3 py-2 text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition-colors"
              >
                Add
              </button>
            </div>
          </div>

          <div className="h-px bg-slate-100" />

          {/* Activity log */}
          <div>
            <label className="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Activity</label>

            <div className="space-y-4 mb-5">
              {activity.length === 0 && (
                <p className="text-xs text-slate-400 text-center py-2">No activity yet.</p>
              )}
              {activity.map((entry) => (
                <div key={entry.id} className="flex gap-3">
                  <Avatar initials={entry.initials} color={entry.avatarColor} size={26} />
                  <div className="flex-1 min-w-0">
                    <div className="flex items-baseline gap-2 mb-1">
                      <span className="text-xs font-semibold text-slate-800">{entry.author}</span>
                      <span className="text-[10px] text-slate-400">{entry.time}</span>
                    </div>
                    {entry.isComment ? (
                      <div className="text-sm text-slate-600 bg-slate-50 border border-slate-100 rounded-xl px-3 py-2 leading-relaxed">
                        {entry.content}
                      </div>
                    ) : (
                      <p className="text-xs text-slate-500 italic">{entry.content}</p>
                    )}
                  </div>
                </div>
              ))}
            </div>

            {/* Comment input */}
            <div className="flex gap-2.5 items-end">
              <Avatar initials="AK" color="#4f46e5" size={26} />
              <div className="flex-1">
                <textarea
                  value={comment}
                  onChange={(e) => setComment(e.target.value)}
                  onKeyDown={(e) => { if (e.key === "Enter" && !e.shiftKey) { e.preventDefault(); postComment(); } }}
                  placeholder="Leave a comment…"
                  rows={2}
                  className="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl bg-white text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent resize-none transition-shadow"
                />
                <button
                  onClick={postComment}
                  disabled={!comment.trim()}
                  className="mt-1.5 px-3 py-1.5 text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                >
                  Post
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}

/* ─── Project Task List View ────────────────────────────────────────────── */
function ProjectTaskListView({
  project,
  onBack,
  onShowModal,
}: {
  project: Project;
  onBack: () => void;
  onShowModal: () => void;
}) {
  const [tasks, setTasks] = useState<ProjectTask[]>(project.tasks);
  const [search, setSearch] = useState("");
  const [filterPriority, setFilterPriority] = useState<Priority | "all">("all");
  const [sortBy, setSortBy] = useState<"dueDate" | "priority" | "name">("dueDate");
  const [groupBy, setGroupBy] = useState<"none" | "priority" | "status">("none");
  const [selectedTask, setSelectedTask] = useState<ProjectTask | null>(null);

  const totalDone = tasks.filter((t) => t.status === "done").length;
  const progress = tasks.length ? Math.round((totalDone / tasks.length) * 100) : 0;

  const PRIORITY_ORDER: Record<Priority, number> = { high: 0, medium: 1, low: 2 };

  const filtered = tasks
    .filter((t) => {
      const matchSearch = !search || t.title.toLowerCase().includes(search.toLowerCase());
      const matchPriority = filterPriority === "all" || t.priority === filterPriority;
      return matchSearch && matchPriority;
    })
    .sort((a, b) => {
      if (sortBy === "priority") return PRIORITY_ORDER[a.priority] - PRIORITY_ORDER[b.priority];
      if (sortBy === "name") return a.title.localeCompare(b.title);
      return 0;
    });

  function completeTask(id: number) {
    setTasks((prev) => prev.map((t) => t.id === id ? { ...t, status: "done" } : t));
  }

  function deleteTask(id: number) {
    setTasks((prev) => prev.filter((t) => t.id !== id));
  }

  function toggleDone(id: number) {
    setTasks((prev) => prev.map((t) => t.id === id
      ? { ...t, status: t.status === "done" ? "todo" : "done" }
      : t));
  }

  const groupedTasks = () => {
    if (groupBy === "none") return [{ label: null, items: filtered }];
    if (groupBy === "priority") {
      return (["high", "medium", "low"] as Priority[]).map((p) => ({
        label: PRIORITY_META[p].label,
        items: filtered.filter((t) => t.priority === p),
      })).filter((g) => g.items.length > 0);
    }
    return (["todo", "in-progress", "review", "done"] as Status[]).map((s) => ({
      label: STATUS_META[s].label,
      items: filtered.filter((t) => t.status === s),
    })).filter((g) => g.items.length > 0);
  };

  return (
    <div>
      {/* Breadcrumb */}
      <div className="flex items-center gap-2 mb-5">
        <button onClick={onBack} className="text-sm text-slate-500 hover:text-indigo-600 font-medium transition-colors flex items-center gap-1.5">
          <svg width="14" height="14" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
            <polyline points="15 18 9 12 15 6" />
          </svg>
          Projects
        </button>
        <svg width="14" height="14" fill="none" stroke="#cbd5e1" strokeWidth="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6" /></svg>
        <span className="text-sm font-semibold text-slate-800">{project.name}</span>
      </div>

      {/* Project header */}
      <div className="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
          <div className="flex items-center gap-3 mb-1">
            <span className="w-3 h-3 rounded-full" style={{ background: project.color }} />
            <h2 className="text-xl font-bold text-slate-900">{project.name}</h2>
          </div>
          <p className="text-sm text-slate-500 ml-6">{tasks.length} tasks · {totalDone} completed</p>
        </div>
        <button
          onClick={onShowModal}
          className="flex items-center gap-1.5 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors shadow-sm"
        >
          <svg width="13" height="13" fill="none" stroke="currentColor" strokeWidth="2.5" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" />
          </svg>
          Add New Task
        </button>
      </div>

      {/* Progress bar */}
      <div className="mb-6 bg-white border border-slate-200 rounded-2xl px-5 py-4 shadow-sm">
        <div className="flex items-center justify-between mb-2">
          <span className="text-xs font-semibold text-slate-600">Overall Progress</span>
          <span className="text-xs font-bold text-indigo-600">{progress}%</span>
        </div>
        <div className="h-2 bg-slate-100 rounded-full overflow-hidden">
          <div
            className="h-full rounded-full transition-all duration-500"
            style={{ width: `${progress}%`, background: `linear-gradient(90deg, #4f46e5, #7c3aed)` }}
          />
        </div>
        <div className="flex items-center gap-4 mt-3">
          {(["todo", "in-progress", "review", "done"] as Status[]).map((s) => {
            const count = tasks.filter((t) => t.status === s).length;
            const m = STATUS_META[s];
            return count > 0 ? (
              <span key={s} className={`text-xs font-medium px-2 py-0.5 rounded-full ${m.color}`}>
                {m.label} · {count}
              </span>
            ) : null;
          })}
        </div>
      </div>

      {/* Controls */}
      <div className="flex flex-wrap items-center gap-2.5 mb-5">
        {/* Search */}
        <div className="relative flex-1 min-w-[180px] max-w-xs">
          <svg className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" width="13" height="13" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" />
          </svg>
          <input
            className="w-full pl-8 pr-3 py-2 text-sm border border-slate-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-300 placeholder-slate-400"
            placeholder="Search tasks…"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
        </div>

        {/* Filter */}
        <div className="flex items-center gap-1.5">
          <svg className="text-slate-400" width="13" height="13" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
          </svg>
          <select
            value={filterPriority}
            onChange={(e) => setFilterPriority(e.target.value as Priority | "all")}
            className="text-sm border border-slate-200 rounded-lg px-2.5 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-300 pr-7"
          >
            <option value="all">All Priorities</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
          </select>
        </div>

        {/* Sort */}
        <div className="flex items-center gap-1.5">
          <svg className="text-slate-400" width="13" height="13" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
            <line x1="3" y1="6" x2="21" y2="6" /><line x1="6" y1="12" x2="18" y2="12" /><line x1="9" y1="18" x2="15" y2="18" />
          </svg>
          <select
            value={sortBy}
            onChange={(e) => setSortBy(e.target.value as typeof sortBy)}
            className="text-sm border border-slate-200 rounded-lg px-2.5 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-300"
          >
            <option value="dueDate">Sort: Due Date</option>
            <option value="priority">Sort: Priority</option>
            <option value="name">Sort: Name</option>
          </select>
        </div>

        {/* Group By */}
        <div className="flex items-center gap-1.5">
          <svg className="text-slate-400" width="13" height="13" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
            <rect x="3" y="3" width="7" height="7" rx="1" /><rect x="14" y="3" width="7" height="7" rx="1" />
            <rect x="3" y="14" width="7" height="7" rx="1" /><rect x="14" y="14" width="7" height="7" rx="1" />
          </svg>
          <select
            value={groupBy}
            onChange={(e) => setGroupBy(e.target.value as typeof groupBy)}
            className="text-sm border border-slate-200 rounded-lg px-2.5 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-300"
          >
            <option value="none">Group By: None</option>
            <option value="priority">Group By: Priority</option>
            <option value="status">Group By: Status</option>
          </select>
        </div>
      </div>

      {/* Task list */}
      <div className="space-y-4">
        {groupedTasks().map((group, gi) => (
          <div key={gi}>
            {group.label && (
              <div className="flex items-center gap-2 mb-2 px-1">
                <span className="text-xs font-bold text-slate-500 uppercase tracking-wide">{group.label}</span>
                <span className="text-xs text-slate-400">({group.items.length})</span>
                <div className="flex-1 h-px bg-slate-100" />
              </div>
            )}
            <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
              {group.items.length === 0 && (
                <div className="px-5 py-8 text-center text-sm text-slate-400">No tasks match your filters.</div>
              )}
              {group.items.map((task, i) => {
                const isDone = task.status === "done";
                return (
                  <div
                    key={task.id}
                    className={`flex items-center gap-4 px-5 py-3.5 cursor-pointer hover:bg-slate-50 transition-colors ${
                      i !== group.items.length - 1 ? "border-b border-slate-100" : ""
                    }`}
                    onClick={() => setSelectedTask(task)}
                  >
                    {/* Circular checkbox */}
                    <div
                      onClick={(e) => { e.stopPropagation(); toggleDone(task.id); }}
                      className={`w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-all ${
                        isDone ? "bg-emerald-500 border-emerald-500" : "border-slate-300 hover:border-indigo-400"
                      }`}
                    >
                      {isDone && (
                        <svg width="9" height="9" fill="none" stroke="white" strokeWidth="2.5" viewBox="0 0 24 24">
                          <polyline points="20 6 9 17 4 12" />
                        </svg>
                      )}
                    </div>

                    {/* Task name */}
                    <p className={`flex-1 text-sm font-medium min-w-0 truncate ${isDone ? "line-through text-slate-400" : "text-slate-800"}`}>
                      {task.title}
                    </p>

                    {/* Priority badge */}
                    <div className="hidden sm:block shrink-0">
                      <PriorityBadge priority={task.priority} />
                    </div>

                    {/* Due date */}
                    <span className={`text-xs font-semibold shrink-0 hidden md:block ${task.overdue && !isDone ? "text-rose-500" : "text-slate-400"}`}>
                      {task.overdue && !isDone ? "⚠ " : ""}{task.dueDate}
                    </span>

                    {/* Assignee avatar */}
                    <Avatar initials={task.initials} color={task.avatarColor} size={26} />
                  </div>
                );
              })}
            </div>
          </div>
        ))}
      </div>

      {/* Task detail panel */}
      {selectedTask && (
        <TaskDetailPanel
          task={selectedTask}
          onClose={() => setSelectedTask(null)}
          onComplete={completeTask}
          onDelete={deleteTask}
        />
      )}
    </div>
  );
}

/* ─── Root App ──────────────────────────────────────────────────────────── */
export default function App() {
  const [loggedIn, setLoggedIn] = useState(false);
  const [view, setView] = useState<View>("dashboard");
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [projects, setProjects] = useState<Project[]>(DEMO_PROJECTS);
  const [activeProjectId, setActiveProjectId] = useState<number | null>(null);
  const [showCreateModal, setShowCreateModal] = useState(false);

  if (!loggedIn) return <LoginScreen onLogin={() => setLoggedIn(true)} />;

  const activeProject = projects.find((p) => p.id === activeProjectId) ?? null;

  return (
    <>
    <div className="min-h-screen bg-slate-50 font-sans flex">
      {/* Sidebar */}
      <aside
        className={`fixed inset-y-0 left-0 z-40 w-60 bg-white border-r border-slate-200 flex flex-col transition-transform duration-200 lg:translate-x-0 ${
          sidebarOpen ? "translate-x-0" : "-translate-x-full"
        } lg:static lg:flex`}
      >
        <div className="px-5 py-5 border-b border-slate-100">
          <div className="flex items-center gap-2.5">
            <div className="w-7 h-7 rounded-lg bg-indigo-600 flex items-center justify-center shrink-0">
              <svg width="14" height="14" fill="none" stroke="white" strokeWidth="2.2" viewBox="0 0 24 24">
                <path d="M9 11l3 3L22 4" />
                <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" />
              </svg>
            </div>
            <span className="text-base font-bold text-slate-900 tracking-tight">Jara</span>
          </div>
        </div>

        <nav className="flex-1 px-3 py-4 space-y-0.5">
          <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-2 mb-2 mt-1">Workspace</p>
          {NAV_ITEMS.filter((n) => n.id !== "settings").map((item) => {
            const active = item.id === view;
            return (
              <button
                key={item.id}
                onClick={() => {
                  setView(item.id as View);
                  setActiveProjectId(null);
                  setSidebarOpen(false);
                }}
                className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all ${
                  active
                    ? "bg-indigo-50 text-indigo-700"
                    : "text-slate-600 hover:bg-slate-50 hover:text-slate-900"
                }`}
              >
                <span className={active ? "text-indigo-600" : "text-slate-400"}>{item.icon}</span>
                {item.label}
              </button>
            );
          })}

          <div className="flex items-center justify-between px-2 mb-2 mt-4">
            <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Projects</p>
            <button
              onClick={() => setShowCreateModal(true)}
              className="text-slate-400 hover:text-indigo-600 transition-colors"
              title="New project"
            >
              <svg width="13" height="13" fill="none" stroke="currentColor" strokeWidth="2.5" viewBox="0 0 24 24">
                <line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" />
              </svg>
            </button>
          </div>
          {projects.map((project) => {
            const isActive = activeProjectId === project.id;
            return (
              <button
                key={project.id}
                onClick={() => {
                  setActiveProjectId(project.id);
                  setSidebarOpen(false);
                }}
                className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all ${
                  isActive ? "bg-indigo-50 text-indigo-700" : "text-slate-600 hover:bg-slate-50 hover:text-slate-900"
                }`}
              >
                <span className="w-2 h-2 rounded-full shrink-0" style={{ background: project.color }} />
                {project.name}
              </button>
            );
          })}
        </nav>

        <div className="px-3 py-4 border-t border-slate-100">
          <div className="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-slate-50 cursor-pointer transition-colors">
            <Avatar initials="AK" color="#4f46e5" size={30} />
            <div className="flex-1 min-w-0">
              <p className="text-xs font-semibold text-slate-800 truncate">Alex Kim</p>
              <p className="text-xs text-slate-400 truncate">alex@jara.app</p>
            </div>
            <svg
              className="text-slate-400 shrink-0"
              width="14"
              height="14"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              viewBox="0 0 24 24"
            >
              <circle cx="12" cy="12" r="1" />
              <circle cx="19" cy="12" r="1" />
              <circle cx="5" cy="12" r="1" />
            </svg>
          </div>
        </div>
      </aside>

      {/* Mobile overlay */}
      {sidebarOpen && (
        <div
          className="fixed inset-0 bg-slate-900/20 z-30 lg:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}

      {/* Main */}
      <div className="flex-1 flex flex-col min-w-0">
        {/* Top bar */}
        <header className="h-14 bg-white border-b border-slate-200 flex items-center px-5 gap-4 sticky top-0 z-20">
          <button
            className="lg:hidden text-slate-500 hover:text-slate-700 transition-colors"
            onClick={() => setSidebarOpen(true)}
          >
            <svg width="20" height="20" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
              <line x1="3" y1="12" x2="21" y2="12" />
              <line x1="3" y1="6" x2="21" y2="6" />
              <line x1="3" y1="18" x2="21" y2="18" />
            </svg>
          </button>
          <div className="flex-1" />
          <button className="relative text-slate-500 hover:text-slate-700 transition-colors">
            <svg width="18" height="18" fill="none" stroke="currentColor" strokeWidth="1.75" viewBox="0 0 24 24">
              <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" />
              <path d="M13.73 21a2 2 0 01-3.46 0" />
            </svg>
            <span className="absolute -top-0.5 -right-0.5 w-2 h-2 bg-rose-500 rounded-full border-2 border-white" />
          </button>
          <button className="text-slate-500 hover:text-slate-700 transition-colors">
            <svg width="18" height="18" fill="none" stroke="currentColor" strokeWidth="1.75" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="3" />
              <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" />
            </svg>
          </button>
          <Avatar initials="AK" color="#4f46e5" size={30} />
        </header>

        <main className="flex-1 px-5 py-7 md:px-8 max-w-[1400px] mx-auto w-full">
          {activeProject ? (
            <ProjectTaskListView
              key={activeProject.id}
              project={activeProject}
              onBack={() => setActiveProjectId(null)}
              onShowModal={() => setShowCreateModal(true)}
            />
          ) : (
            <>
              {view === "dashboard" && <DashboardView tasks={TASKS} onNavigate={setView} />}
              {view === "list" && <ListView tasks={TASKS} />}
              {view === "kanban" && <KanbanView tasks={TASKS} />}
            </>
          )}
        </main>
      </div>
    </div>

    {showCreateModal && (
      <CreateProjectModal
        onClose={() => setShowCreateModal(false)}
        onCreate={(name) => {
          const colors = ["#4f46e5","#7c3aed","#0ea5e9","#10b981","#f59e0b","#f43f5e"];
          const newProject: Project = {
            id: Date.now(),
            name,
            color: colors[projects.length % colors.length],
            tasks: [],
          };
          setProjects((prev) => [...prev, newProject]);
          setActiveProjectId(newProject.id);
        }}
      />
    )}
    </>
  );
}
